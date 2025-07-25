<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
    include 'connect.php';

    // Fetch hallId from the URL parameter
    $hallId = isset($_GET['id']) ? $_GET['id'] : '';

    // Fetch hall name if hallId is provided
    if ($hallId) {
        // Fetch hall details using the hallId
        $hallQuery = "SELECT * FROM hall WHERE id = '$hallId'";
        $hallResult = mysqli_query($con, $hallQuery);
        $hall = mysqli_fetch_assoc($hallResult);
        $hallName = $hall['name'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
        $_SESSION['form_data'] = $_POST;
        // Get form data

        $packageName = mysqli_real_escape_string($con, $_POST['packageName']);
        $priceWithSofa = isset($_POST['priceWithSofa']) && $_POST['priceWithSofa'] !== '' ? mysqli_real_escape_string($con, $_POST['priceWithSofa']) : 0;
        $priceWithSofaWithMenu = mysqli_real_escape_string($con, $_POST['priceWithSofaWithMenu']);
        $priceWithChair = isset($_POST['priceWithChair']) && $_POST['priceWithChair'] !== '' ? mysqli_real_escape_string($con, $_POST['priceWithChair']) : 0;
        $priceWithChairWithMenu = mysqli_real_escape_string($con, $_POST['priceWithChairWithMenu']);
        $priceWithMix = isset($_POST['priceWithMix']) && $_POST['priceWithMix'] !== '' ? mysqli_real_escape_string($con, $_POST['priceWithMix']) : 0;
        $priceWithMixWithMenu = mysqli_real_escape_string($con, $_POST['priceWithMixWithMenu']);
        
        // Menu items - at least one meat dish must be provided
        $menuStarter = mysqli_real_escape_string($con, $_POST['menuStarter']);
        $menuChicken = !empty($_POST['menuChicken']) ? mysqli_real_escape_string($con, $_POST['menuChicken']) : 'None';
        $menuBeef = !empty($_POST['menuBeef']) ? mysqli_real_escape_string($con, $_POST['menuBeef']) : 'None';
        $menuMutton = !empty($_POST['menuMutton']) ? mysqli_real_escape_string($con, $_POST['menuMutton']) : 'None';
        $menuDesert = mysqli_real_escape_string($con, $_POST['menuDesert']);
        $menuSalad = mysqli_real_escape_string($con, $_POST['menuSalad']);
        $menuDrink = mysqli_real_escape_string($con, $_POST['menuDrink']);
        
        // Validate at least one meat dish is provided
        if ($menuChicken === 'None' && $menuBeef === 'None' && $menuMutton === 'None') {
            echo "<script>alert('Error: At least one meat dish (Chicken, Beef, or Mutton) must be provided!'); window.location.href = '" . $_SERVER['REQUEST_URI'] . "';</script>";
            exit();

        }
        
        // Other fields
        $discount = mysqli_real_escape_string($con, $_POST['discount']);
        $acCharges = mysqli_real_escape_string($con, $_POST['acCharges']);
        $acRates = mysqli_real_escape_string($con, $_POST['rates']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $detail = mysqli_real_escape_string($con, $_POST['detail']);
        $hallId = $_POST['hallId'];

        // Check if package already exists
        $checkQuery = "SELECT * FROM package WHERE packageName = '$packageName' AND hallId = '$hallId'";
        $checkResult = mysqli_query($con, $checkQuery);

        if(mysqli_num_rows($checkResult) > 0) {
            echo "<script>alert('Error: This package already exists for the selected hall!');</script>";
        } else {
            $sql = "INSERT INTO package (packageName, priceWithSofa, priceWithSofaWithMenu, priceWithChair, priceWithChairWithMenu, 
                    priceWithMix, priceWithMixWithMenu, menuStarter, menuChicken, menuBeef, menuMutton, menuDesert, 
                    menuSalad, menuDrinks, acChargesPrices, acRates, discount, status, menuDetail, hallId) 
                    VALUES ('$packageName', '$priceWithSofa', '$priceWithSofaWithMenu', '$priceWithChair', '$priceWithChairWithMenu',
                    '$priceWithMix', '$priceWithMixWithMenu', '$menuStarter', '$menuChicken', '$menuBeef', '$menuMutton', 
                    '$menuDesert', '$menuSalad', '$menuDrink', '$acCharges', '$acRates', '$discount', '$status', '$detail', '$hallId')";

            if (mysqli_query($con, $sql)) {
                echo "<script>
                    alert('Package added successfully!');
                    window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                </script>";
            } else {
                echo "Error: " . mysqli_error($con);
            }
        }
                    
    }
    // Handle delete request
    if (isset($_GET['deletePackage'])) {
        $deleteId = mysqli_real_escape_string($con, $_GET['deletePackage']);
        $deleteQuery = "DELETE FROM package WHERE packageId='$deleteId'";
        if (mysqli_query($con, $deleteQuery)) {
            echo "<script>
                alert('Package deleted successfully!');
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
            </script>";
            exit(); // Refresh the page
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
?>

<div class="main" style="overflow-x:hidden">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Add Package</h2>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="hallId" value="<?= $hallId ?>">
            <div class="form-row">
                <!-- Package Information -->
            <div class="col-md-3 mb-3">
                <label for="packageName" class="font-weight-bold">Package Title</label>
                <input type="text" class="form-control" id="packageName" name="packageName" required placeholder="Enter Package Title" value="<?php echo isset($_POST['packageName']) ? $_POST['packageName'] : ''; ?>">
            </div>

            <!-- Pricing Options -->
            <div class="col-md-3 mb-3">
                <label for="priceWithSofa" class="font-weight-bold">Sofa Seating (Excluding Menu)</label>
                <input type="number" class="form-control" id="priceWithSofa" placeholder="Enter Price" name="priceWithSofa" value="<?php echo isset($_POST['priceWithSofa']) ? $_POST['priceWithSofa'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithSofaWithMenu" class="font-weight-bold">Sofa Seating (Including Menu)</label>
                <input type="number" class="form-control" id="priceWithSofaWithMenu" placeholder="Enter Price" name="priceWithSofaWithMenu" required value="<?php echo isset($_POST['priceWithSofaWithMenu']) ? $_POST['priceWithSofaWithMenu'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithChair" class="font-weight-bold">Chair Seating (Excluding Menu)</label>
                <input type="number" class="form-control" id="priceWithChair" placeholder="Enter Price" name="priceWithChair" value="<?php echo isset($_POST['priceWithChair']) ? $_POST['priceWithChair'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithChairWithMenu" class="font-weight-bold">Chair Seating (Including Menu)</label>
                <input type="number" class="form-control" id="priceWithChairWithMenu" placeholder="Enter Price" name="priceWithChairWithMenu" required value="<?php echo isset($_POST['priceWithChairWithMenu']) ? $_POST['priceWithChairWithMenu'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithMix" class="font-weight-bold">Mixed Seating (Excluding Menu)</label>
                <input type="number" class="form-control" id="priceWithMix" placeholder="Enter Price" name="priceWithMix" value="<?php echo isset($_POST['priceWithMix']) ? $_POST['priceWithMix'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithMixWithMenu" class="font-weight-bold">Mixed Seating (Including Menu)</label>
                <input type="number" class="form-control" id="priceWithMixWithMenu" placeholder="Enter Price" name="priceWithMixWithMenu" required value="<?php echo isset($_POST['priceWithMixWithMenu']) ? $_POST['priceWithMixWithMenu'] : ''; ?>">
            </div>

            <!-- Menu Items -->
            <div class="col-md-3 mb-3">
                <label for="menuStarter" class="font-weight-bold">Starter Dish</label>
                <input type="text" class="form-control" id="menuStarter" placeholder="Enter Starter Dish" name="menuStarter" required value="<?php echo isset($_POST['menuStarter']) ? $_POST['menuStarter'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuChicken" class="font-weight-bold">Chicken Dish</label>
                <input type="text" class="form-control" id="menuChicken" placeholder="Enter Chicken Dish" name="menuChicken" value="<?php echo isset($_POST['menuChicken']) ? $_POST['menuChicken'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuBeef" class="font-weight-bold">Beef Dish</label>
                <input type="text" class="form-control" id="menuBeef" placeholder="Enter Beef Dish" name="menuBeef" value="<?php echo isset($_POST['menuBeef']) ? $_POST['menuBeef'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuMutton" class="font-weight-bold">Mutton Dish</label>
                <input type="text" class="form-control" id="menuMutton" placeholder="Enter Mutton Dish" name="menuMutton" value="<?php echo isset($_POST['menuMutton']) ? $_POST['menuMutton'] : ''; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuDesert" class="font-weight-bold">Dessert</label>
                <input type="text" class="form-control" id="menuDesert" placeholder="Enter Dessert" name="menuDesert" required value="<?php echo isset($_POST['menuDesert']) ? $_POST['menuDesert'] : ''; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="menuSalad" class="font-weight-bold">Salad Option</label>
                <input type="text" class="form-control" id="menuSalad" placeholder="Enter Salad Option" name="menuSalad" required  value="<?php echo isset($_POST['menuSalad']) ? $_POST['menuSalad'] : ''; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="menuDrink" class="font-weight-bold">Drink</label>
                <input type="text" class="form-control" id="menuDrink" placeholder="Enter Drink" name="menuDrink" required value="<?php echo isset($_POST['menuDrink']) ? $_POST['menuDrink'] : ''; ?>">
            </div>

            <!-- Discount -->
            <div class="col-md-4 mb-3">
                <label for="discount" class="font-weight-bold">Discount Percentage</label>
                <input type="number" class="form-control" id="discount" placeholder="Enter Discount" name="discount" required value="<?php echo isset($_POST['discount']) ? $_POST['discount'] : ''; ?>">
            </div>
            <!-- Ac Charges -->
            <div class="col-md-4 mb-3">
                <label for="acCharges" class="font-weight-bold">AC Charges</label>
                <input type="number" class="form-control" id="acCharges" placeholder="Enter Price" name="acCharges" required value="<?php echo isset($_POST['acCharges']) ? $_POST['acCharges'] : ''; ?>">
            </div>

            <div class="col-md-4 mb-3">
                <label for="rates" class="font-weight-bold">Rate Details</label>
                <select class="form-control" id="rates" name="rates" required>
                    <option value="perPerson" <?= (isset($_POST['perPerson']) && $_POST['perPerson'] == 'perPerson') ? 'selected' : '' ?>>Per Person</option>
                    <option value="perHour"  <?= (isset($_POST['perHour']) && $_POST['perHour'] == 'perHour') ? 'selected' : '' ?>>Per Hour</option>
                </select>
            </div>

            <!-- Status -->
            <div class="col-md-4 mb-3">
                <label for="status" class="font-weight-bold">Package Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="active" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive"  <?= (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <!-- Additional Details -->
            <div class="col-md-12 mb-3">
                <label for="detail" class="font-weight-bold">Package Description</label>
                <textarea name="detail" id="detail" class="form-control" rows="3" placeholder="Enter Package Description" required><?php echo isset($_POST['detail']) ? $_POST['detail'] : ''; ?></textarea>
            </div>
            </div>
            <button class="btn btn-primary" type="submit" name="submit">Add Package</button>
        </form>

        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
        <table id="myTable">
            <thead>
                <tr>
                   <th>Sr No.</th>
                   <th>Hall Name</th>
                   <th>Package Name</th>
                   <th>Sofa Seating (Excluding Menu)</th>
                   <th>Sofa Seating (Including Menu)</th>
                   <th>Chair Seating (Excluding Menu)</th>
                   <th>Chair Seating (Including Menu)</th>
                   <th>Mixed Seating (Excluding Menu)</th>
                   <th>Mixed Seating (Including Menu)</th>
                   <th>Starter Dish</th>
                   <th>Chicken Dish</th>
                   <th>Beef Dish</th>
                   <th>Mutton Dish</th>
                   <th>Dessert</th>
                   <th>Salad Option</th>
                   <th>Drink</th>
                   <th>Discount Percentage</th>
                   <th>AC Charges</th>
                   <th>AC Rates</th>
                   <th>Package Status</th>
                   <th>Package Description</th>
                   <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch data from the service table to display
                include 'connect.php';
                
                // Step 1: Fetch all roles and create a mapping array
                $hallsQuery = "SELECT * FROM hall";
                $hallsResult = mysqli_query($con, $hallsQuery);
    
                $hallMapping = [];
                while ($hallRow = mysqli_fetch_assoc($hallsResult)) {
                    $hallMapping[$hallRow['id']] = $hallRow['name'];
                }
                $query = "SELECT * FROM package WHERE hallId = '$hallId' ORDER BY packageId DESC";
                $result = mysqli_query($con, $query);

                if(mysqli_num_rows($result) > 0){
                    $sr = 1;
                    while ($row = mysqli_fetch_assoc($result)){
                        $hallName = isset($hallMapping[$row['hallId']]) ? $hallMapping[$row['hallId']] : "Unknown hall";
                        echo "<tr>
                            <td>{$sr}</td>
                            <td>{$hallName}</td>
                            <td>{$row['packageName']}</td>
                            <td>{$row['priceWithSofa']}</td>
                            <td>{$row['priceWithSofaWithMenu']}</td>
                            <td>{$row['priceWithChair']}</td>
                            <td>{$row['priceWithChairWithMenu']}</td>
                            <td>{$row['priceWithMix']}</td>
                            <td>{$row['priceWithMixWithMenu']}</td>
                            <td>{$row['menuStarter']}</td>
                            <td>{$row['menuChicken']}</td>
                            <td>{$row['menuBeef']}</td>
                            <td>{$row['menuMutton']}</td>
                            <td>{$row['menuDesert']}</td>
                            <td>{$row['menuDrinks']}</td>
                            <td>{$row['menuSalad']}</td>
                            <td>{$row['discount']}</td>
                            <td>{$row['acChargesPrices']}</td>
                            <td>{$row['acRates']}</td>
                            <td>{$row['status']}</td>
                            <td>".nl2br(htmlspecialchars($row['menuDetail']))."</td>
                            <td class='text-center'>
                               <div class='d-inline-flex justify-content-center'>
                                <a href='updatePackage.php?id={$row['packageId']}&hallId={$row['hallId']}' class='btn btn-warning btn-sm mr-2' title='Edit'><ion-icon name='create-outline'></ion-icon></a>
                                <a href='?deletePackage={$row['packageId']}' class='mr-2 btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")' title='Delete'><ion-icon name='trash-outline'></ion-icon></a>
                               </div>
                            </td>
                        </tr>";
                        $sr++;
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No Services Found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>  