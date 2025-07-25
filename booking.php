
<?php include'./include/startingSection.php'; ?>
    <link rel="stylesheet" href="assets/css/booking.css" />
  </head>
  <body>

  <?php
session_start();
include 'admin/connect.php';
if (!isset($_SESSION['customerId'])) {
    header('Location: login.php');
    exit();
}
function showSweetAlert($icon, $title, $text, $redirect = null) {
    // Escape single quotes and newlines in the text
    $text = addslashes(str_replace(["\r", "\n"], '', $text));
    echo "<script>
        Swal.fire({
            icon: '".addslashes($icon)."',
            title: '".addslashes($title)."',
            html: '".$text."',
            showConfirmButton: true,
            timer: 5000
        })";
    if ($redirect) {
        echo ".then((result) => { if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) { window.location.href = '".addslashes($redirect)."'; }})";
    }
    echo "; </script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book'])) {
    $_SESSION['form_data'] = $_POST;
    $includeAC = ($_POST['includeAC'] == '1');
    // Determine menu based on service type
    switch($_POST['serviceType']) {
        case 'sittingOnly':
            $menu = "No menu - sitting only";
            break;
        case 'packageMenu':
            $menu = mysqli_real_escape_string($con, $_POST['menu']);
            break;
        case 'customPackage':
            $menu = mysqli_real_escape_string($con, $_POST['customMenu']);
            break;
        default:
            $menu = '';
            $packageType = 'manual';
    }

    $cnic = trim($_POST['cnic']);
    // Remove all non-numeric characters from CNIC
    $cnic = preg_replace('/[^0-9]/', '', $cnic);
    $phoneNo = trim($_POST['phoneNo']);
    // Remove all non-numeric characters from CNIC
    $phoneNo = preg_replace('/[^0-9]/', '', $phoneNo);

    // Store all form data in session
    $_SESSION['booking_data'] = [

        'customerId' => $_SESSION['customerId'],
        'packageId' => mysqli_real_escape_string($con, $_GET['id']),
        'hallId' => mysqli_real_escape_string($con, $_GET['hallId']),
        'serviceType' => $_POST['serviceType'],
        // 'packageType' => $packageType, 
        'fullName' => mysqli_real_escape_string($con, $_POST['fullName']),
        'email' => mysqli_real_escape_string($con, $_POST['email']),
        'phoneNo' => mysqli_real_escape_string($con, $phoneNo),
        'cnic' => mysqli_real_escape_string($con, $cnic),
        'dateOfBooking' => mysqli_real_escape_string($con, $_POST['dateOfBooking']),
        'timeSlot' => $_POST['timeSlot'],
        'totalSeats' => mysqli_real_escape_string($con, $_POST['totalSeats']),
        'sittingType' => $_POST['sittingType'],
        'menu' => $menu,
        'address' => mysqli_real_escape_string($con, $_POST['address']),
        'orderPrice' => 0, // Will be calculated
        'discount' => 0, // Will be calculated
        'totalPrice' => 0 // Will be calculated
    ];

    // Get hall details including capacity and event capacity
    $hallQuery = "SELECT capacity, event_capacity FROM hall WHERE id = '{$_SESSION['booking_data']['hallId']}'";
    $hallResult = mysqli_query($con, $hallQuery);
    $hall = mysqli_fetch_assoc($hallResult);

    // 1. FIRST CHECK FUNCTION CAPACITY
    $functionsQuery = "SELECT COUNT(*) AS current_functions 
                      FROM booking 
                      WHERE hallId = '{$_SESSION['booking_data']['hallId']}' 
                      AND dateOfBooking = '{$_SESSION['booking_data']['dateOfBooking']}' 
                      AND timeSlot = '{$_SESSION['booking_data']['timeSlot']}'";
    
    $functionsResult = mysqli_query($con, $functionsQuery);
    $functionsData = mysqli_fetch_assoc($functionsResult);
    
    // If function capacity is full, reject immediately regardless of seating
    if ($functionsData['current_functions'] >= $hall['event_capacity']) {
        showSweetAlert('error', 'Booking Error', 'This time slot already has the maximum '.$hall['event_capacity'].' events booked. No more events can be added to this time slot.', 'booking.php?id='.$_SESSION['booking_data']['packageId'].'&hallId='.$_SESSION['booking_data']['hallId']);
        exit();
    }
    // 2. THEN CHECK SEATING CAPACITY (only if function slots available)
    // Check if this booking would exceed per-event seating capacity
    if ($_SESSION['booking_data']['totalSeats'] > $hall['capacity']) {
        showSweetAlert('error', 'Capacity Exceeded', 'Maximum seating capacity for one event is '.$hall['capacity'].'. Please reduce your guest count.', 'booking.php?id='.$_SESSION['booking_data']['packageId'].'&hallId='.$_SESSION['booking_data']['hallId']);
        exit();
    }

    // Check total attendees across concurrent events
    $attendeesQuery = "SELECT SUM(totalSeats) AS total_attendees 
                      FROM booking 
                      WHERE hallId = '{$_SESSION['booking_data']['hallId']}' 
                      AND dateOfBooking = '{$_SESSION['booking_data']['dateOfBooking']}' 
                      AND timeSlot = '{$_SESSION['booking_data']['timeSlot']}'";
    
    $attendeesResult = mysqli_query($con, $attendeesQuery);
    $attendeesData = mysqli_fetch_assoc($attendeesResult);
    $currentAttendees = $attendeesData['total_attendees'] ?? 0;
    
    if (($currentAttendees + $_SESSION['booking_data']['totalSeats']) > $hall['capacity']) {
        $remainingCapacity = $hall['capacity'] - $currentAttendees;
        showSweetAlert('error', 'Limited Capacity', 'This time slot only has capacity for '.$remainingCapacity.' more guests. Please reduce your guest count or choose another time.', 'booking.php?id='.$_SESSION['booking_data']['packageId'].'&hallId='.$_SESSION['booking_data']['hallId']);
        exit();
    }

    $packageQuery = "SELECT * FROM package WHERE packageId = '{$_SESSION['booking_data']['packageId']}'";
        $packageResult = mysqli_query($con, $packageQuery);
        $package = mysqli_fetch_assoc($packageResult);

    if ($_SESSION['booking_data']['serviceType'] != 'customPackage') {
    
        // Determine if this is a sitting-only booking
        $isSittingOnly = ($_POST['serviceType'] == 'sittingOnly');
        
        // Price calculation based on sitting type
        switch($_SESSION['booking_data']['sittingType']) {
            case 'Sofa':
                $orderPrice = $isSittingOnly ? $package['priceWithSofa'] : $package['priceWithSofaWithMenu'];
                break;
            case 'Chair':
                $orderPrice = $isSittingOnly ? $package['priceWithChair'] : $package['priceWithChairWithMenu'];
                break;
            case 'Mix':
                $orderPrice = $isSittingOnly ? $package['priceWithMix'] : $package['priceWithMixWithMenu'];
                break;
            default:
                $orderPrice = 0;
        }

        $acRates = $package['acRates'] === 'perPerson'? 'per Person' : 'per Hour';    
        if ($includeAC) {
            if($acRates === 'per Person') {
                $acCharges = $package['acChargesPrices'] * $_SESSION['booking_data']['totalSeats'];
            } else {
                $acCharges = $package['acChargesPrices'] * 3; // Flat rate for high AC charges
            }
        } else {
            $acCharges = 0; // No AC charges if not selected
        }
        $_SESSION['booking_data']['acCharges'] = $acCharges;
        $_SESSION['booking_data']['orderPrice'] = $orderPrice * $_SESSION['booking_data']['totalSeats'] + $acCharges;
        $_SESSION['booking_data']['includeAC'] = $includeAC;
        $_SESSION['booking_data']['discount'] = $package['discount'];
        $_SESSION['booking_data']['totalPrice'] = $_SESSION['booking_data']['orderPrice'] - ($_SESSION['booking_data']['orderPrice'] * $package['discount'] / 100);
        $_SESSION['booking_data']['status'] = 'confirmed';
    } else {
        // For custom package
        $_SESSION['booking_data']['orderPrice'] = 0;
        $_SESSION['booking_data']['discount'] = 0;
        $_SESSION['booking_data']['totalPrice'] = 0;
        $_SESSION['booking_data']['status'] = 'pending';
        $_SESSION['booking_data']['acCharges'] = 0;
        $_SESSION['booking_data']['includeAC'] = $includeAC;
    }

    // Redirect to payment page
    header("Location: payment.php");
    exit();
}
?>

    <!--header section starts-->

    <?php
    include'./include/header.php'; ?>

    <!--header section ends-->

    <section class="booking">
           <div class="packageTable">
            <?php
            include 'admin/connect.php'; 
            $packageId = isset($_GET['id']) ? $_GET['id'] : '';
            $hallId = isset($_GET['hallId']) ? $_GET['hallId'] : '';
            
            if (empty($packageId) || empty($hallId)) {
                header('Location: halls.php'); // or show an error message
                exit();
            }
            if($packageId){
                $packageQuery = "SELECT * FROM package WHERE packageId = '$packageId'";
            $packageResult = mysqli_query($con, $packageQuery);
            if (mysqli_num_rows($packageResult) > 0) {
                $package = mysqli_fetch_assoc($packageResult);
                $acRates = $package['acRates'] === 'perPerson'? 'per Person' : 'per Hour';

                echo "<div class='box'>
                    <h3 class='title'>{$package['packageName']}</h3>
                    <div class='package-content'>
                        <div class='pricing-details'>
                            <h4>Seating Options</h4>
                            <ul>";
                            if($package['priceWithSofa'] != 0): 
                                echo "<li><i class='fas fa-couch'></i> <strong>Sofa Seating:</strong> {$package['priceWithSofa']}</li>";
                            endif; 
                            echo"
                                <li><i class='fas fa-couch'></i> <strong>Sofa Seating With Menu:</strong> {$package['priceWithSofaWithMenu']}</li>";
                            if($package['priceWithChair'] != 0): 
                                echo "<li><i class='fas fa-chair'></i> <strong>Chair Seating:</strong> {$package['priceWithChair']}</li>";
                            endif;
                                echo"<li><i class='fas fa-chair'></i> <strong>Chair Seating With Menu:</strong> {$package['priceWithChairWithMenu']}</                        li>";
                            if($package['priceWithMix'] != 0): 
                                echo"<li><i class='fas fa-random'></i> <strong>Sofa and Chair Seating:</strong> {$package['priceWithMix']}</li>";
                            endif;
                                echo"<li><i class='fas fa-random'></i> <strong>Sofa, Chair Seating With Menu:</strong> {$package['priceWithMixWithMenu']}</li>
                            </ul>
                        </div>
                        <div class='menu-details'>
                            <h4>Menu Details</h4>
                            <ul>
                                <li><i class='fas fa-utensils'></i> <strong>Starter:</strong> {$package['menuStarter']}</li>
                                <li><i class='fas fa-drumstick-bite'></i> <strong>Chicken:</strong> {$package['menuChicken']}</li>
                                <li><i class='fas fa-hamburger'></i> <strong>Beef:</strong> {$package['menuBeef']}</li>
                                <li><i class='fas fa-bacon'></i> <strong>Mutton:</strong> {$package['menuMutton']}</li>
                                <li><i class='fas fa-ice-cream'></i> <strong>Desert:</strong> {$package['menuDesert']}</li>
                                <li><i class='fas fa-glass-whiskey'></i> <strong>Drinks:</strong> {$package['menuDrinks']}</li>
                                <li><i class='fas fa-leaf'></i> <strong>Salad:</strong> {$package['menuSalad']}</li>
                                <li><i class='fas fa-fan'></i> <strong>AC Charges:</strong> {$package['acChargesPrices']} {$acRates}</li>
                                <li><i class='fas fa-check'></i> <strong>Menu Detail:</strong> {$package['menuDetail']}</li>
                            </ul>
                        </div>
                    </div>
                </div>";
            }}
            ?>
           </div>
           <div class="bookingForm">

              <h1 class="heading"> Booking <span>form</span> </h1>
              <div class="contact">
              <form action="" method="POST" id="bookingForm" autocomplete="off">
                <!-- Hidden fields -->
                <input type="hidden" name="packageId" value="<?php echo $packageId; ?>">
                <input type="hidden" name="hallId" value="<?php echo $hallId; ?>">
                <input type="hidden" id="menuData" value="<?php echo htmlspecialchars(json_encode($package)); ?>">
                <input type="hidden" id="acCharges" value="<?php echo htmlspecialchars($package['acChargesPrices']); ?>">
    
                <div class="form-group">
                    <label for="serviceType">Service Type</label>
                    <select id="serviceType" class="form-control" name="serviceType" required>
                        <option value="">Select Service Type</option>
                        <?php if($package['priceWithSofa'] != 0 && $package['priceWithChair'] != 0 && $package['priceWithMix'] != 0): ?>
                        <option value="sittingOnly">Sitting Only (No Menu)</option>
                        <?php endif; ?>
                        <option value="packageMenu">Full Package (With Standard Menu)</option>
                        <option value="customPackage">Custom Package (Customize Everything)</option>
                    </select>
                </div>

                <!-- Personal Information -->
                <div class="inputBox">
                   <div class="form-group">
                       <label for="fullName">Full Name</label>
                       <input type="text" id="fullName" class="form-control" placeholder="Enter your full name" name="fullName" required value="<?php echo isset($_POST['fullName']) ? $_POST['fullName'] : ''; ?>">
                   </div>
    
                   <div class="form-group">
                       <label for="email">Email</label>
                       <input type="email" id="email" class="form-control" placeholder="Enter your email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                   </div>
                </div>
    
                <div class="inputBox">
                   <div class="form-group">
                       <label for="phoneNo">Mobile Number</label>
                       <input type="tel" id="phoneNo" class="form-control" placeholder="Enter mobile number" name="phoneNo" required value="<?php echo isset($_POST['phoneNo']) ? $_POST['phoneNo'] : ''; ?>">
                   </div>
    
                   <div class="form-group">
                       <label for="cnic">CNIC</label>
                       <input type="text" id="cnic" class="form-control" placeholder="Enter CNIC number" name="cnic" required value="<?php echo isset($_POST['cnic']) ? $_POST['cnic'] : ''; ?>">
                   </div>
                </div>
                

                <!-- Booking Details -->
                <div class="inputBox">
                   <div class="form-group">
                       <label for="dateOfBooking">Date of Booking</label>
                       <input type="date" id="dateOfBooking" class="form-control" name="dateOfBooking" required min="<?php echo date('Y-m-d'); ?>"  value="<?php echo isset($_POST['dateOfBooking']) ? $_POST['dateOfBooking'] : ''; ?>"
                       >
                   </div>
    
                   <div class="form-group">
                       <label for="timeSlot">Time Slot</label>
                       <select id="timeSlot" class="form-control" name="timeSlot" required>
                           <option value="">Select Time Slot</option>
                           <option value="afternoon">Afternoon (1pm to 4pm)</option>
                           <option value="evening">Evening (7pm to 10pm)</option>
                       </select>
                   </div>
                </div>

                <div class="inputBox">
                    <div class="form-group">
                        <label for="totalSeats">Total Seats</label>
                        <input type="number" id="totalSeats" class="form-control" placeholder="Enter total number of seats" name="totalSeats" required value="<?php echo isset($_POST['totalSeats']) ? $_POST['totalSeats'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="sittingType">Sitting Type</label>
                        <select id="sittingType" class="form-control" name="sittingType" required>
                            <option value="">Select Sitting Type</option>
                            <option value="Sofa">Sofa</option>
                            <option value="Chair">Chair</option>
                            <option value="Mix">Mix (Sofa + Chair)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="includeAC">Include AC?</label>
                    <select id="includeAC" class="form-control" name="includeAC" required>
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <!-- Add this div to show live pricing in your booking form -->
                <div class="livePricing" >
                    <h3>Price Summary</h3>
                    <div id="priceDetails">
                        <p>Order Price: <span id="orderPriceDisplay">0</span> PKR</p>
                        <p>Discount: <span id="discountDisplay">0</span> %</p>
                        <p>AC Charges: <span id="acPriceDisplay">0</span> PKR</p>
                        <p>Total Price: <span id="totalPriceDisplay">0</span> PKR</p>
                        <p>25% Deposit Required: <span id="depositDisplay">0</span> PKR</p>
                    </div>
                </div>

                <div id="customFields" style="display: none;">
                    <div class="form-group">
                        <label for="customMenu">Custom Menu Requirements</label>
                        <textarea id="customMenu" class="form-control" name="customMenu" 
                        placeholder="Describe your custom menu requirements..." rows="3"></textarea>
                    </div>
                </div>

                <div class="form-group"  id="manualFields">
                    <label for="menu">Selected Menu</label>
                    <textarea id="menu" class="form-control" name="menu" placeholder="Menu will be auto-filled based on selection" rows="5" readonly></textarea>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">Full Address</label>
                    <textarea id="address" class="form-control" name="address" placeholder="Enter your complete address" rows="5" required></textarea>
                </div>
    
                <button type="submit" name="book" class="btn btn-primary">Book Now</button>
            </form>
                
              </div>
           </div>
        <!-- </div> -->
        
    </section>

    <!-- footer section starts -->

    <?php include'./include/footer.php'; ?>

    <!-- footer section ends -->

    <!-- theme toggler starts-->

    <?php include'./include/themeToggler.php'; ?>

    <!-- theme toggler ends -->
   <script src="assets/js/booking.js"></script>

<?php include'./include/scriptSection.php'; ?>