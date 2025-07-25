<?php
include 'connect.php';

// Fetch hallId from the URL parameter
$hallId = isset($_GET['id']) ? $_GET['id'] : '';

// Initialize service options as an empty array
$services = [];

// Fetch hall name if hallId is provided
if ($hallId) {
    // Fetch hall details using the hallId
    $hallQuery = "SELECT * FROM hall WHERE id = '$hallId'";
    $hallResult = mysqli_query($con, $hallQuery);
    $hall = mysqli_fetch_assoc($hallResult);
    $hallName = $hall['name'];

    // Fetch services related to the selected hallId
    $serviceQuery = "SELECT * FROM service WHERE hallId = '$hallId' ORDER BY name ASC";
    $serviceResult = mysqli_query($con, $serviceQuery);
    $services = mysqli_fetch_all($serviceResult, MYSQLI_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $hallId = $_POST['hallId'];  // Get selected hall ID
    $serviceId = $_POST['serviceId'];  // Get selected service ID
    
    // Handle multiple image uploads
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    $imageNames = $_FILES['galleryImages']['name'];
    $imageTmpNames = $_FILES['galleryImages']['tmp_name'];
    $imageSizes = $_FILES['galleryImages']['size'];
    $imageErrors = $_FILES['galleryImages']['error'];

    foreach ($imageNames as $key => $imageName) {
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        if (in_array($imageExt, $allowedExtensions)) {
            if ($imageErrors[$key] === 0) {
                if ($imageSizes[$key] < 5000000) {
                    $newImageName = uniqid('IMG-', true) . '.' . $imageExt;
                    $imageDestination = "uploads/" . $newImageName;

                    move_uploaded_file($imageTmpNames[$key], $imageDestination);

                    // Insert data into the gallery table
                    $sql = "INSERT INTO `gallery` (`hallId`, `serviceId`, `galleryImage`) 
                            VALUES ('$hallId', '$serviceId', '$newImageName')";

                    if (!mysqli_query($con, $sql)) {
                        echo "Error: " . mysqli_error($con);
                    }
                } else {
                    echo "<script>alert('File size too large!');</script>";
                }
            } else {
                echo "<script>alert('Error uploading file!');</script>";
            }
        } else {
            echo "<script>alert('Invalid file type!');</script>";
        }
    }
}

 // Handle delete request
 if (isset($_GET['deleteGallery'])) {
    $deleteId = mysqli_real_escape_string($con, $_GET['deleteGallery']);
    $deleteQuery = "DELETE FROM gallery WHERE galleryId=$deleteId";
    if (mysqli_query($con, $deleteQuery)) {
        echo "<script>
            alert('Gallery deleted successfully!');
            window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
        </script>";
        exit(); // Refresh the page
    } else {
        echo "Error: " . mysqli_error($con);
    }
}
?>


<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main" style="overflow-x:hidden;">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Gallery Form</h2>
            <form method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="form-row">
                    <!-- Hall Name (fixed) -->
                    <div class="col-md-4 mb-3">
                        <label for="hallName" class="font-weight-bold">Hall Name</label>
                        <input type="text" class="form-control" id="hallName" value="<?= isset($hallName) ? $hallName : '' ?>" disabled>
                        <input type="hidden" name="hallId" value="<?= $hallId ?>">
                    </div>

                    <!-- Service Dropdown (filtered by hallId) -->
                    <div class="col-md-4 mb-3">
                        <label for="serviceId" class="font-weight-bold">Select Service</label>
                        <select class="form-control" id="serviceId" name="serviceId" required>
                            <option value="">Select Service</option>
                            <?php
                            // Display services related to the selected hallId
                            if ($services) {
                                foreach ($services as $service) {
                                    echo "<option value='{$service['serviceId']}'>{$service['name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- File Uploads -->
                    <div class="col-md-4 mb-3">
                        <label for="galleryImages" class="font-weight-bold">Upload Images</label>
                        <input type="file" class="form-control" id="galleryImages" name="galleryImages[]" multiple required>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit" name="submit">Add Gallery</button>
            </form>

                <table class="table table-bordered table-striped table-hover mt-3 col-md-6" id="myTable">
                <thead class="table-primary">
                    <tr>
                        <th>Sr No.</th>
                        <th>Hall Name</th>
                        <th>Service Name</th>
                        <th>Images</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $galleryQuery = "SELECT * FROM gallery WHERE hallId = '$hallId' ORDER BY galleryId DESC";
                $galleryResult = mysqli_query($con, $galleryQuery);

                if (mysqli_num_rows($galleryResult) > 0) {
                    $sr = 1;
                    while ($row = mysqli_fetch_assoc($galleryResult)) {
                        // Fetch the hall name using the hallId
                        $hallQuery = "SELECT name FROM hall WHERE id = '{$row['hallId']}'";
                        $hallResult = mysqli_query($con, $hallQuery);
                        $hall = mysqli_fetch_assoc($hallResult);
                        $hallName = $hall['name'];  // Assign hall name

                        // Fetch the service name using the serviceId
                        $serviceQuery = "SELECT name FROM service WHERE serviceId = '{$row['serviceId']}'";
                        $serviceResult = mysqli_query($con, $serviceQuery);
                        $service = mysqli_fetch_assoc($serviceResult);
                        $serviceName = $service['name'];  // Assign service name
                        echo "<tr>
                            <td>{$sr}</td>
                            <td>{$hallName}</td>
                            <td>{$serviceName}</td>
                            <td><img src='uploads/{$row['galleryImage']}' alt='Gallery Image' width='80' height='50'></td>
                            <td class='text-center'>
                                <a href='?deleteGallery={$row['galleryId']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>
                                <ion-icon name='trash-outline'></ion-icon>
                                </a>
                            </td>
                        </tr>";
                        $sr++;
                    }
                }
                ?>
                </tbody>
            </table>


    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>
<?php include './includes/footer.php'; ?>
