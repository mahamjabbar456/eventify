<?php
include 'connect.php';

// Fetch the roleId from the URL to get the existing role details
if (isset($_GET['id'])) {
    $roleId = $_GET['id'];
    // Query to fetch role details by roleId
    $query = "SELECT * FROM role WHERE roleId = $roleId";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $roleName = $row['roleName'];
        $existingImage = $row['roleImage'];
    } else {
        echo "<script>alert('Role not found!'); window.location='role.php';</script>";
        exit();
    }
}

// Handle form submission for updating role details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $roleName = mysqli_real_escape_string($con,$_POST['roleName']);

    // Handle the image upload (if a new image is uploaded)
    if (!empty($_FILES['roleImage']['name'])) {
        $imageName = $_FILES['roleImage']['name'];
        $imageTmpName = $_FILES['roleImage']['tmp_name'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imageExt, $allowedExtensions)) {
            $newImageName = uniqid('IMG-', true) . '.' . $imageExt;
            $imagePath = "uploads/" . $newImageName;

            // Delete the old image if a new one is uploaded
            if (!empty($existingImage) && file_exists("uploads/" . $existingImage)) {
                unlink("uploads/" . $existingImage); // Delete old image
            }

            move_uploaded_file($imageTmpName, $imagePath); // Move the new image
        } else {
            echo "<script>alert('Invalid file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
            exit();
        }
    } else {
        $newImageName = $existingImage; // Keep the old image if no new image is uploaded
    }

    // Update the role in the database
    $sql = "UPDATE role SET roleName = '$roleName', roleImage = '$newImageName' WHERE roleId = $roleId";
    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Role updated successfully!');window.location='role.php';</script>";
    } else {
        echo "Error: " . mysqli_error($con);
    }
}
?>

<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Update Role</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="roleId" value="<?= $roleId ?>">

            <div class="form-row">
                <div class="col-md-6 mb-3">
                    <label for="roleName" class="font-weight-bold">Role Name</label>
                    <input type="text" class="form-control" id="roleName" name="roleName" value="<?= $roleName ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="roleImage" class="font-weight-bold">Role Image</label>
                    <div style="margin-top:-8px;" class="d-flex align-items-center">
                        <input type="file" class="form-control" id="roleImage" name="roleImage">
                        <img src="uploads/<?= $existingImage ?>" alt="Role Image" width="60">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-primary" type="submit" name="update">Update Role</button>
                <a href="role.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>
