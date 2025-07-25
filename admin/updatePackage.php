<?php
// Include database connection file
include 'connect.php';

// Check if the 'id' and 'hallId' parameters are set in the URL
if (isset($_GET['id']) && isset($_GET['hallId'])) {
    $packageId = $_GET['id'];
    $hallId = $_GET['hallId'];

    $hallQuery = "SELECT * FROM hall WHERE id = '$hallId'";
    $hallResult = mysqli_query($con, $hallQuery);
    $hall = mysqli_fetch_assoc($hallResult);
    $hallName = $hall['name'];

    // Query to fetch service details
    $query = "SELECT * FROM package WHERE packageId = $packageId AND hallId = $hallId";
    $result = mysqli_query($con, $query);

    // If service is found, fetch its details
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $packageName = $row['packageName'];
        $priceWithSofa = $row['priceWithSofa'];
        $priceWithSofaWithMenu = $row['priceWithSofaWithMenu'];
        $priceWithChair = $row['priceWithChair'];
        $priceWithChairWithMenu = $row['priceWithChairWithMenu'];
        $priceWithMix = $row['priceWithMix'];
        $priceWithMixWithMenu = $row['priceWithMixWithMenu'];
        $menuStarter = $row['menuStarter'];
        $menuChicken = $row['menuChicken'];
        $menuBeef = $row['menuBeef'];
        $menuMutton = $row['menuMutton'];
        $menuDesert = $row['menuDesert'];
        $menuSalad = $row['menuSalad'];
        $menuDrinks = $row['menuDrinks'];
        $discount = $row['discount'];
        $acCharges = $row['acChargesPrices'];
        $acRates = $row['acRates'];
        $status = $row['status'];
        $menuDetail = $row['menuDetail'];
    } else {
        echo "<script>alert('Package not found!'); window.location = 'addPackage.php?id={$hallId}';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request!'); window.location = 'addPackage.php?id={$hallId}';</script>";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $packageId = $_POST['packageId'];
    $hallId = $_POST['hallId'];

    $packageName = mysqli_real_escape_string($con, $_POST['packageName']);
    // Make prices optional (default to 0)
    $priceWithSofa = isset($_POST['priceWithSofa']) && $_POST['priceWithSofa'] !== '' ? mysqli_real_escape_string($con, $_POST['priceWithSofa']) : 0;
    $priceWithSofaWithMenu = mysqli_real_escape_string($con, $_POST['priceWithSofaWithMenu']);
    $priceWithChair = isset($_POST['priceWithChair']) && $_POST['priceWithChair'] !== '' ? mysqli_real_escape_string($con, $_POST['priceWithChair']) : 0;
    $priceWithChairWithMenu = mysqli_real_escape_string($con, $_POST['priceWithChairWithMenu']);
    $priceWithMix = isset($_POST['priceWithMix']) && $_POST['priceWithMix'] !== '' ? mysqli_real_escape_string($con, $_POST['priceWithMix']) : 0;
    $priceWithMixWithMenu = mysqli_real_escape_string($con, $_POST['priceWithMixWithMenu']);
    
    // Menu items - at least one meat dish required
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
    $discount = mysqli_real_escape_string($con, $_POST['discount']);
    $acCharges = mysqli_real_escape_string($con, $_POST['acCharges']);
    $acRates = mysqli_real_escape_string($con, $_POST['rates']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $detail = mysqli_real_escape_string($con, $_POST['detail']);

    // Check if package name exists (excluding current package)
    $checkQuery = "SELECT * FROM package WHERE packageName = '$packageName' AND hallId = '$hallId' AND packageId != $packageId";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Error: A package with this name already exists for the selected hall. Please choose a different name.');</script>";
    } else {
        $sql = "UPDATE package SET 
                    packageName = '$packageName',
                    priceWithSofa = '$priceWithSofa',
                    priceWithSofaWithMenu = '$priceWithSofaWithMenu',
                    priceWithChair = '$priceWithChair',
                    priceWithChairWithMenu = '$priceWithChairWithMenu',
                    priceWithMix = '$priceWithMix',
                    priceWithMixWithMenu = '$priceWithMixWithMenu',
                    menuStarter = '$menuStarter',
                    menuChicken = '$menuChicken',
                    menuBeef = '$menuBeef',
                    menuMutton = '$menuMutton',
                    menuDesert = '$menuDesert',
                    menuSalad = '$menuSalad',
                    menuDrinks = '$menuDrink',
                    discount = '$discount',
                    acChargesPrices = '$acCharges',
                    acRates = '$acRates',
                    status = '$status',
                    menuDetail = '$detail'
                WHERE packageId = $packageId AND hallId = $hallId";

        if (mysqli_query($con, $sql)) {
            
            echo "<script>
                alert('Package updated successfully!');
                window.location = 'addPackage.php?id={$hallId}';
            </script>";
            exit();
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
}

?>

<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Update Service</h2>
        <form method="post" enctype="multipart/form-data" autocomplete = "off">
            <input type="hidden" name="packageId" value="<?= $packageId ?>">
            <input type="hidden" name="hallId" value="<?= $hallId ?>">

            <div class="form-row">
            <div class="col-md-3 mb-3">
                <label for="packageName" class="font-weight-bold">Package Title</label>
                <input type="text" class="form-control" id="packageName" required name="packageName" value="<?= $packageName ?>" >
            </div>

            <!-- Pricing Options -->
            <div class="col-md-3 mb-3">
                <label for="priceWithSofa" class="font-weight-bold">Sofa Seating (Excluding Menu)</label>
                <input type="number" class="form-control" id="priceWithSofa" name="priceWithSofa" value="<?= $priceWithSofa ?>" >
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithSofaWithMenu" class="font-weight-bold">Sofa Seating (Including Menu)</label>
                <input type="number" class="form-control" required id="priceWithSofaWithMenu" name="priceWithSofaWithMenu" value=<?= $priceWithSofaWithMenu ?>>
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithChair" class="font-weight-bold">Chair Seating (Excluding Menu)</label>
                <input type="number" class="form-control" id="priceWithChair" name="priceWithChair" value=<?= $priceWithChair ?> >
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithChairWithMenu" class="font-weight-bold">Chair Seating (Including Menu)</label>
                <input type="number" class="form-control" required id="priceWithChairWithMenu" name="priceWithChairWithMenu"  value=<?= $priceWithChairWithMenu ?> >
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithMix" class="font-weight-bold">Mixed Seating (Excluding Menu)</label>
                <input type="number" class="form-control" id="priceWithMix" name="priceWithMix" value=<?= $priceWithMix ?> >
            </div>
            <div class="col-md-3 mb-3">
                <label for="priceWithMixWithMenu" class="font-weight-bold">Mixed Seating (Including Menu)</label>
                <input type="number" class="form-control" required id="priceWithMixWithMenu" name="priceWithMixWithMenu" value=<?= $priceWithMixWithMenu ?> >
            </div>

            <!-- Menu Items -->
            <div class="col-md-3 mb-3">
                <label for="menuStarter" class="font-weight-bold">Starter Dish</label>
                <input type="text" class="form-control" id="menuStarter" required name="menuStarter" value="<?= $menuStarter ?>" >
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuChicken" class="font-weight-bold">Chicken Dish</label>
                <input type="text" class="form-control" id="menuChicken" name="menuChicken" value="<?= $menuChicken ?>" >
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuBeef" class="font-weight-bold">Beef Dish</label>
                <input type="text" class="form-control" id="menuBeef" name="menuBeef" value="<?= $menuBeef ?>" >
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuMutton" class="font-weight-bold">Mutton Dish</label>
                <input type="text" class="form-control" id="menuMutton" name="menuMutton" value="<?= $menuMutton ?>" >
            </div>
            <div class="col-md-3 mb-3">
                <label for="menuDesert" class="font-weight-bold">Desert</label>
                <input type="text" class="form-control" id="menuDesert" required name="menuDesert" value="<?= $menuDesert ?>"        >
            </div>
            <div class="col-md-4 mb-3">
                <label for="menuSalad" class="font-weight-bold">Salad Option</label>
                <input type="text" class="form-control" id="menuSalad" required name="menuSalad" value="<?= $menuSalad ?> ">
            </div>
            <div class="col-md-4 mb-3">
                <label for="menuDrink" class="font-weight-bold">Drink</label>
                <input type="text" class="form-control" id="menuDrink" required name="menuDrink" value="<?= $menuDrinks ?>">
            </div>

            <!-- Discount -->
            <div class="col-md-4 mb-3">
                <label for="discount" class="font-weight-bold">Discount Percentage</label>
                <input type="number" class="form-control" id="discount" name="discount" value=<?= $discount ?> >
            </div>
            <!-- Ac Charges -->
            <div class="col-md-4 mb-3">
                <label for="acCharges" class="font-weight-bold">AC Charges</label>
                <input type="number" class="form-control" id="acCharges" placeholder="Enter Price" name="acCharges" required value="<?= $acCharges ?>">
            </div>

            <div class="col-md-4 mb-3">
                <label for="rates" class="font-weight-bold">Rate Details</label>
                <select class="form-control" id="rates" name="rates" required>
                    <option value="perPerson" <?= ($acRates == 'perPerson') ? 'selected' : '' ?>>Per Person</option>
                    <option value="perHour" <?= ($acRates == 'perHour') ? 'selected' : '' ?>>Per Hour</option>
                </select>
            </div>

            <!-- Status -->
            <div class="col-md-4 mb-3">
                <label for="status" class="font-weight-bold">Package Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <!-- Additional Details -->
            <div class="col-md-12 mb-3">
                <label for="detail" class="font-weight-bold">Package Description</label>
                <textarea name="detail" id="detail" class="form-control" rows="3" required><?= htmlspecialchars       ($menuDetail) ?></textarea>
            </div>
            

            </div>
            <div class="form-group">
                <button class="btn btn-primary" type="submit" name="update">Update Package</button>
                <a href="addPackage.php?id=<?php echo $hallId; ?>" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>
