<?php
// Include database connection file
include 'connect.php';

// Check if the 'id' and 'hallId' parameters are set in the URL
if (isset($_GET['id']) && isset($_GET['hallId'])) {
    $service_id = $_GET['id'];
    $hallId = $_GET['hallId'];

    $hallQuery = "SELECT * FROM hall WHERE id = '$hallId'";
    $hallResult = mysqli_query($con, $hallQuery);
    $hall = mysqli_fetch_assoc($hallResult);
    $hallName = $hall['name'];

    // Query to fetch service details
    $query = "SELECT * FROM service WHERE serviceId = $service_id AND hallId = $hallId";
    $result = mysqli_query($con, $query);

    // If service is found, fetch its details
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $serviceName = $row['name'];
        $existingImage = $row['image'];
        $existingBackground = $row['background'];
        $detail = $row['detail'];
        $tagline = $row['tagline'];
        $status = $row['status'];
    } else {
        echo "<script>alert('Service not found!'); window.location = 'addService.php?id={$hallId}';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request!'); window.location = 'addService.php?id={$hallId}';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $service_id = $_POST['service_id'];
    $hallId = $_POST['hallId'];

    $serviceName = mysqli_real_escape_string($con, $_POST['serviceName']);
    $detail = mysqli_real_escape_string($con, $_POST['detail']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    
    // Make tagline optional (can be NULL)
    $tagline = !empty($_POST['tagline']) ? mysqli_real_escape_string($con, $_POST['tagline']) : NULL;

    // Check if the service name already exists
    $checkQuery = "SELECT * FROM service WHERE name = '$serviceName' AND hallId = '$hallId' AND serviceId != $service_id";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Error: A service with this name already exists for the selected hall.');</script>";
    } else {
        // Initialize image variables
        $newImageName = $existingImage; // Keep existing if no new upload
        $newBackgroundName = $existingBackground; // Keep existing if no new upload

        // Handle optional image upload
        if (isset($_POST['removeImage']) && $_POST['removeImage'] == '1') {
            if (!empty($existingImage) && file_exists("uploads/" . $existingImage)) {
                unlink("uploads/" . $existingImage);
            }
            $newImageName = NULL;
        } 
        else if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $imageTmpName = $_FILES['image']['tmp_name'];
            $imageExt = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($imageExt, $allowedExtensions)) {
                $newImageName = uniqid('IMG-', true) . '.' . $imageExt;
                move_uploaded_file($imageTmpName, "uploads/" . $newImageName);

                // Delete old image if it exists
                if (!empty($existingImage) && file_exists("uploads/" . $existingImage)) {
                    unlink("uploads/" . $existingImage);
                }
            } else {
                echo "<script>alert('Invalid image file type!');</script>";
                exit();
            }
        }else{
            $newImageName = $existingImage;
        }

        // Handle background upload (required)
        if (!empty($_FILES['background']['name'])) {
            $background = $_FILES['background']['name'];
            $backgroundTmpName = $_FILES['background']['tmp_name'];
            $backgroundExt = strtolower(pathinfo($background, PATHINFO_EXTENSION));

            if (in_array($backgroundExt, $allowedExtensions)) {
                $newBackgroundName = uniqid('BG-', true) . '.' . $backgroundExt;
                move_uploaded_file($backgroundTmpName, "uploads/" . $newBackgroundName);

                // Delete old background if it exists
                if (!empty($existingBackground) && file_exists("uploads/" . $existingBackground)) {
                    unlink("uploads/" . $existingBackground);
                }
            } else {
                echo "<script>alert('Invalid background file type!');</script>";
                exit();
            }
        }

        // Prepare SQL values
        $imageValue = $newImageName !== NULL ? "'$newImageName'" : "NULL";
        $taglineValue = $tagline !== NULL ? "'$tagline'" : "NULL";

        // Update service details
        $sql = "UPDATE service SET 
                    name = '$serviceName',
                    image = $imageValue,
                    background = '$newBackgroundName',
                    detail = '$detail',
                    tagline = $taglineValue,
                    status = '$status'
                WHERE serviceId = $service_id AND hallId = $hallId";

        if (mysqli_query($con, $sql)) {
            echo "<script>
                alert('Service updated successfully!');
                window.location = 'addService.php?id={$hallId}';
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
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="service_id" value="<?= $service_id ?>">
            <input type="hidden" name="hallId" value="<?= $hallId ?>">

            <div class="form-row">
                <div class="col-md-6 mb-3">
                    <label for="hallName" class="font-weight-bold">Hall Name</label>
                    <input type="text" class="form-control" id="hallName" name="hallName" value="<?= $hallName ?>" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="serviceName" class="font-weight-bold">Service Name</label>
                    <input type="text" class="form-control" id="serviceName" name="serviceName" value="<?= $serviceName ?>" required >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tagline" class="font-weight-bold">Tagline</label>
                    <input type="text" class="form-control" id="tagline" name="tagline" value="<?= $tagline ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="font-weight-bold">Service Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($status == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="image" class="me-2 font-weight-bold">Image (Optional)</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control mt-0 mr-3" id="image" name="image" style="width: auto;">
                        <?php if (!empty($existingImage)): ?>
                            <img src="uploads/<?= $existingImage ?>" alt="Current Image" width="60" class="rounded">
                            <div class="form-check ml-2">
                                <input class="form-check-input" type="checkbox" name="removeImage" id="removeImage" value="1">
                                <label class="form-check-label" for="removeImage">Remove</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>


                <div class="col-md-6 mb-3">
                    <label for="background" class="me-2 font-weight-bold">Background</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control mt-0 mr-3" id="background" name="background" style="width:auto;">
                        <img src="uploads/<?= $existingBackground ?>" alt="Background" width="60" class="rounded">
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label for="detail" class="font-weight-bold">Detail</label>
                    <textarea name="detail" id="detail" class="form-control" rows="3" required><?= htmlspecialchars($detail) ?></textarea>
                </div>

            </div>
            <div class="form-group">
                <button class="btn btn-primary" type="submit" name="update">Update Service</button>
                <a href="addService.php?id=<?php echo $hallId; ?>" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>
