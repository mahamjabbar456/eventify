<?php
// Include the database connection file
include 'connect.php';

// Check if the 'id' parameter is set in the URL to fetch hall details
if (isset($_GET['id'])) {
    $hall_id = $_GET['id'];
    // Query to fetch hall details from the database
    $query = "SELECT * FROM hall WHERE id = $hall_id";
    $result = mysqli_query($con, $query);
    
    // If the hall is found, fetch its details and store in variables
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $hallName = $row['name'];
        $address = $row['address'];
        $capacity = $row['capacity'];
        $phoneNo = $row['contactNo'];
        $hallDetail = $row['detail'];
        $existingLogo = $row['logo'];
        $existingCover = $row['cover'];
        $event_capacity = $row['event_capacity'];
        $status = $row['status'];
    } else {
        // If hall is not found, show an alert and redirect
        echo "<script>alert('Hall not found!'); window.location='hall.php';</script>";
        exit();
    }
}

// Handle form submission for updating hall details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // Retrieve the updated values from the form input fields
    $hall_id = mysqli_real_escape_string($con, $_POST['hall_id']);
    $hallName = mysqli_real_escape_string($con, $_POST['hallName']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $capacity = mysqli_real_escape_string($con, $_POST['capacity']);
    $event_capacity = mysqli_real_escape_string($con, $_POST['event_capacity']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $phoneNo = mysqli_real_escape_string($con, str_replace('-', '', $_POST['phoneNo']));
    $hallDetail = mysqli_real_escape_string($con, $_POST['hallDetail']);

    // Allowed file extensions for the uploaded images
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Handle Logo Image upload
    if (!empty($_FILES['logo']['name'])) {
        $logoImage = $_FILES['logo']['name'];
        $logoTmpName = $_FILES['logo']['tmp_name'];
        $logoExt = strtolower(pathinfo($logoImage, PATHINFO_EXTENSION));
        // Check if the uploaded logo has a valid extension
        if (in_array($logoExt, $allowedExtensions)) {
            $newLogoName = uniqid('LOGO-', true) . '.' . $logoExt;
            move_uploaded_file($logoTmpName, "uploads/" . $newLogoName); // Move uploaded file to 'uploads' folder
            
            // If there's an old logo, delete it
            if (!empty($existingLogo) && file_exists("uploads/" . $existingLogo)) {
                unlink("uploads/" . $existingLogo); // Delete old logo
            }
        } else {
            echo "<script>alert('Invalid logo file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
            exit(); // Exit if invalid file type
        }
    } else {
        // If no new logo uploaded, retain the existing logo
        $newLogoName = $existingLogo;
    }

    // Handle Cover Image upload
    if (!empty($_FILES['cover']['name'])) {
        $coverImage = $_FILES['cover']['name'];
        $coverTmpName = $_FILES['cover']['tmp_name'];
        $coverExt = strtolower(pathinfo($coverImage, PATHINFO_EXTENSION));
        // Check if the uploaded cover has a valid extension
        if (in_array($coverExt, $allowedExtensions)) {
            $newCoverName = uniqid('COVER-', true) . '.' . $coverExt;
            move_uploaded_file($coverTmpName, "uploads/" . $newCoverName); // Move uploaded file to 'uploads' folder
            
            // If there's an old cover, delete it
            if (!empty($existingCover) && file_exists("uploads/" . $existingCover)) {
                unlink("uploads/" . $existingCover); // Delete old cover
            }
        } else {
            echo "<script>alert('Invalid cover file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
            exit(); // Exit if invalid file type
        }
    } else {
        // If no new cover uploaded, retain the existing cover
        $newCoverName = $existingCover;
    }

    // SQL query to update the hall details in the database
    $sql = "UPDATE hall SET 
                name = '$hallName', 
                address = '$address', 
                capacity = '$capacity', 
                contactNo = '$phoneNo', 
                detail = '$hallDetail', 
                logo = '$newLogoName', 
                cover = '$newCoverName' ,
                event_capacity = '$event_capacity', 
                status = '$status' 
            WHERE id = $hall_id";

    // Execute the query and check if the update was successful
    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Hall updated successfully!');window.location='hall.php';</script>";
    } else {
        echo "Error: " . mysqli_error($con); // Show error message if query fails
    }
}
?>

<!-- Include header and sidebar components -->
<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Update Hall</h2>
        <!-- Form for updating hall details -->
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="hall_id" value="<?= $hall_id ?>">

            <div class="form-row">
                <!-- Hall Name input field -->
                <div class="col-md-4 mb-3">
                    <label for="hallName" class="font-weight-bold">Hall Name</label>
                    <input type="text" class="form-control" id="hallName" name="hallName" value="<?= $hallName ?>" required>
                </div>
                <!-- Address input field -->
                <div class="col-md-4 mb-3">
                    <label for="address" class="font-weight-bold">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= $address ?>" required>
                </div>
                <!-- Phone No input field -->
                <div class="col-md-4 mb-3">
                    <label for="phoneNo" class="font-weight-bold">Phone No</label>
                    <input type="tel" class="form-control" id="phoneNo" name="phoneNo" value="<?= $phoneNo ?>" required>
                </div>
                <!-- Capacity input field -->
                <div class="col-md-4 mb-3">
                    <label for="capacity" class="font-weight-bold">Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" value="<?= $capacity ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="event_capacity" class="font-weight-bold">Event Capacity</label>
                    <input type="number" class="form-control" id="event_capacity" name="event_capacity" value="<?= $event_capacity ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status" class="font-weight-bold">Hall Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($status == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="pending" <?= ($status == 'pending') ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>
                <!-- Logo Image upload field -->
                <div class="col-md-6 mb-3">
                    <label for="logo" class="font-weight-bold">Logo Image</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control" id="logo" name="logo">
                        <img src="uploads/<?= $existingLogo ?>" alt="Logo" width="60">
                    </div>
                </div>
                <!-- Cover Image upload field -->
                <div class="col-md-6 mb-3">
                    <label for="cover" class="font-weight-bold">Cover Image</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control" id="cover" name="cover">
                        <img src="uploads/<?= $existingCover ?>" alt="Cover" width="60">
                    </div>
                </div>
                <!-- Hall Details input field -->
                <div class="col-md-12 mb-3">
                    <label for="hallDetail" class="font-weight-bold">Detail</label>
                    <!-- <input type="text" class="form-control" id="hallDetail" name="hallDetail" value="<?= $hallDetail ?>" required> -->
                    <textarea name="hallDetail" id="hallDetail" rows="3" class="form-control"><?= htmlspecialchars($hallDetail) ?></textarea>
                </div>
            </div>
            <!-- Submit button -->
            <div class="form-group">
                <button class="btn btn-primary" type="submit" name='update'>Update Hall</button>
                <a href="hall.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<script>
    $(document).ready(function() {
    // Phone number masking
    $('input[name="phoneNo"]').inputmask({
        mask: '0399-9999999',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
});
</script>

<!-- Include footer component -->
<?php include './includes/footer.php'; ?>
