<?php 
include './include/startingSection.php'; 
include 'admin/connect.php';

// Get search parameters
$dateTimeInput = isset($_GET['date']) ? $_GET['date'] : '';
$function = isset($_GET['function']) ? $_GET['function'] : '';
$seating = isset($_GET['seating']) ? (int)$_GET['seating'] : 0;
$price = isset($_GET['price']) ? (float)$_GET['price'] : 0;

// Simplified function to check hall availability (without time slots)
function checkHallAvailability($con, $hallId, $date, $requiredSeats) {
    // Get hall capacity details
    $hallQuery = "SELECT capacity, event_capacity FROM hall WHERE id = '$hallId'";
    $hallResult = mysqli_query($con, $hallQuery);
    $hall = mysqli_fetch_assoc($hallResult);

    if (!$hall) {
        return ['available' => false, 'message' => 'Hall not found'];
    }

    // 1. Check total events for the day (regardless of time slot)
    $functionsQuery = "SELECT COUNT(*) AS current_functions 
                     FROM booking 
                     WHERE hallId = '$hallId' 
                     AND dateOfBooking = '$date'";
    $functionsResult = mysqli_query($con, $functionsQuery);
    $functionsData = mysqli_fetch_assoc($functionsResult);

    if ($functionsData['current_functions'] < $hall['event_capacity']) {
        // 2. Check seating capacity
        if ($requiredSeats <= $hall['capacity']) {
            // 3. Check total attendees across all events that day
            $attendeesQuery = "SELECT SUM(totalSeats) AS total_attendees 
                             FROM booking 
                             WHERE hallId = '$hallId' 
                             AND dateOfBooking = '$date'";
            $attendeesResult = mysqli_query($con, $attendeesQuery);
            $attendeesData = mysqli_fetch_assoc($attendeesResult);

            $currentAttendees = $attendeesData['total_attendees'] ?? 0;
            if (($currentAttendees + $requiredSeats) <= $hall['capacity']) {
                return ['available' => true];
            }
        }
    }

    return ['available' => false, 'message' => 'Hall not available'];
}

$functionFirstWord = explode(' ', trim($function))[0];

// Get halls matching the function type (first word only)
$serviceQuery = "SELECT DISTINCT s.hallId 
                FROM service s 
                WHERE s.name LIKE '".mysqli_real_escape_string($con, $functionFirstWord)."%' 
                AND s.status = 'active'";
$serviceResult = mysqli_query($con, $serviceQuery);
$hallIds = [];
while ($service = mysqli_fetch_assoc($serviceResult)) {
   $hallIds[] = $service['hallId'];
}
$halls = [];
if (!empty($hallIds)) {
    $hallIdList = implode(',', $hallIds);
    $hallQuery = "SELECT h.* FROM hall h
                WHERE h.id IN ($hallIdList) 
                AND h.status = 'active'";
    $hallResult = mysqli_query($con, $hallQuery);
    
    while ($hall = mysqli_fetch_assoc($hallResult)) {
        // Check hall availability (without time slot)
        $availability = checkHallAvailability($con, $hall['id'], $dateTimeInput, $seating);

        // Get just the first image for this hall's service
        $imageQuery = "SELECT s.background 
                       FROM service s 
                       WHERE s.hallId = {$hall['id']} 
                       AND s.name LIKE '".mysqli_real_escape_string($con, $functionFirstWord)."%'
                       LIMIT 1";
        $imageResult = mysqli_query($con, $imageQuery);
        $image = mysqli_fetch_assoc($imageResult);
        
        // Store the image path with the hall data
        $hall['imagePath'] = $image['background'] ?? 'default-hall.jpg';
        
        if ($availability['available']) {
            // Get packages where any price field is <= specified price
            $packageQuery = "SELECT * FROM package
                           WHERE hallId = {$hall['id']}
                           AND (
                               (priceWithSofa > 0 AND priceWithSofa <= $price) OR
                               (priceWithSofaWithMenu > 0 AND priceWithSofaWithMenu <= $price) OR
                               (priceWithChair > 0 AND priceWithChair <= $price) OR
                               (priceWithChairWithMenu > 0 AND priceWithChairWithMenu <= $price) OR
                               (priceWithMix > 0 AND priceWithMix <= $price) OR
                               (priceWithMixWithMenu > 0 AND priceWithMixWithMenu <= $price)
                           )";
            $packageResult = mysqli_query($con, $packageQuery);
            $packages = mysqli_fetch_all($packageResult, MYSQLI_ASSOC);
            
            // Inside the package processing loop (after getting $packages)
            if (!empty($packages)) {
                $uniquePackages = [];
                $pricePatterns = [];
                
                foreach ($packages as $package) {
                    // Get all basic seating options (without menu) that match user's price
                    $basicSeatingOptions = [];
                    
                    if ($package['priceWithSofa'] > 0 && $package['priceWithSofa'] <= $price) {
                        $basicSeatingOptions['Sofa'] = $package['priceWithSofa'];
                    }
                    if ($package['priceWithChair'] > 0 && $package['priceWithChair'] <= $price) {
                        $basicSeatingOptions['Chair'] = $package['priceWithChair'];
                    }
                    if ($package['priceWithMix'] > 0 && $package['priceWithMix'] <= $price) {
                        $basicSeatingOptions['Mix'] = $package['priceWithMix'];
                    }
                    
                    // Get all menu-inclusive options that match user's price
                    $menuOptions = [];
                    if ($package['priceWithSofaWithMenu'] > 0 && $package['priceWithSofaWithMenu'] <= $price) {
                        $menuOptions['SofaWithMenu'] = $package['priceWithSofaWithMenu'];
                    }
                    if ($package['priceWithChairWithMenu'] > 0 && $package['priceWithChairWithMenu'] <= $price) {
                        $menuOptions['ChairWithMenu'] = $package['priceWithChairWithMenu'];
                    }
                    if ($package['priceWithMixWithMenu'] > 0 && $package['priceWithMixWithMenu'] <= $price) {
                        $menuOptions['MixWithMenu'] = $package['priceWithMixWithMenu'];
                    }
                    
                    // Create a unique signature of the basic seating options
                    $basicSignature = md5(serialize($basicSeatingOptions));
                    
                    // Always include packages with menu options
                    if (!empty($menuOptions)) {
                        $package['availableOptions'] = array_merge($basicSeatingOptions, $menuOptions);
                        $package['showMenu'] = true;
                        $uniquePackages[] = $package;
                    }
                    // For basic seating only packages, check if we've already shown this combination
                    elseif (!empty($basicSeatingOptions) && !in_array($basicSignature, $pricePatterns)) {
                        $pricePatterns[] = $basicSignature;
                        $package['availableOptions'] = $basicSeatingOptions;
                        $package['showMenu'] = false;
                        $uniquePackages[] = $package;
                    }
                }
                
                if (!empty($uniquePackages)) {
                    $hall['packages'] = $uniquePackages;
                    $halls[] = $hall;
                }
            }
        }
    }
}
?>

<link rel="stylesheet" href="assets/css/findFunction.css">
</head>
<body>

<!--header section starts-->
<?php 
session_start();
include './include/header.php'; 
?>
<!--header section ends-->

<section class="specificPackage">
    <h1 class="heading"><span><?= htmlspecialchars($function) ?></span> Function</h1>
    <?php if (empty($halls)): ?>
    <div class="no-results">
        <p>No available halls found matching your criteria. Please try different search parameters.</p>
        <a href="index.php" class="btn">Modify Search</a>
    </div>
    <?php else: ?>
        <div class="box1">
            <?php foreach ($halls as $hall): ?>
                <?php foreach ($hall['packages'] as $package): ?>
                    <div class="functionPackage">
                        <div class="left">
                            <img src="admin/uploads/<?= htmlspecialchars($hall['imagePath']) ?>" alt="<?= htmlspecialchars($hall['name']) ?>">
                        </div>
                        <div class="right">
                            <h2>Hall Name: <span><?= htmlspecialchars($hall['name']) ?></span></h2>
                            <h4>Available Seating Options Within Your Budget (Rs. <?= $price ?>):</h4>
                            <ul>
                                <?php foreach ($package['availableOptions'] as $option => $optionPrice): ?>
                                    <li>
                                        <?php if (strpos($option, 'WithMenu') !== false): ?>
                                            <i class="fas fa-utensils"></i>
                                            <strong><?= str_replace('WithMenu', ' With Menu', $option) ?>:</strong>
                                        <?php elseif ($option === 'Sofa'): ?>
                                            <i class="fas fa-couch"></i>
                                            <strong>Sofa Seating:</strong>
                                        <?php elseif ($option === 'Chair'): ?>
                                            <i class="fas fa-chair"></i>
                                            <strong>Chair Seating:</strong>
                                        <?php else: ?>
                                            <i class="fas fa-random"></i>
                                            <strong>Mixed Seating:</strong>
                                        <?php endif; ?>
                                        Rs. <?= htmlspecialchars($optionPrice) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <?php if ($package['showMenu']): ?>
                                <h4>Menu Options</h4>
                                <div class="menu">
                                    <ul>
                                        <li><i class='fas fa-utensils'></i> <strong>Starter:</strong> <?= htmlspecialchars($package['menuStarter']) ?></li>
                                        <li><i class='fas fa-drumstick-bite'></i> <strong>Chicken:</strong> <?= htmlspecialchars($package['menuChicken']) ?></li>
                                        <li><i class='fas fa-hamburger'></i> <strong>Beef:</strong> <?= htmlspecialchars($package['menuBeef']) ?></li>
                                        <li><i class='fas fa-bacon'></i> <strong>Mutton:</strong> <?= htmlspecialchars($package['menuMutton']) ?></li>
                                        <li><i class='fas fa-ice-cream'></i> <strong>Dessert:</strong> <?= htmlspecialchars($package['menuDesert']) ?></li>
                                        <li><i class='fas fa-glass-whiskey'></i> <strong>Drinks:</strong> <?= htmlspecialchars($package['menuDrinks']) ?></li>
                                        <li><i class='fas fa-leaf'></i> <strong>Salad:</strong> <?= htmlspecialchars($package['menuSalad']) ?></li>
                                        <li><i class='fas fa-fan'></i> <strong>AC Charges:</strong> Rs. <?= htmlspecialchars($package['acChargesPrices']) ?></li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                             <a href='booking.php?id=<?= isset($package['packageId']) ? htmlspecialchars($package['packageId']) : '' ?>&hallId=<?= htmlspecialchars($hall['id']) ?>' class='btn'>Book This Package</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- footer section starts -->
<?php include './include/footer.php'; ?>
<!-- footer section ends -->

<!-- theme toggler starts-->
<?php include './include/themeToggler.php'; ?>
<!-- theme toggler ends -->

<?php include './include/scriptSection.php'; ?>