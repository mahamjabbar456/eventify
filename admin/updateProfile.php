<?php
include 'connect.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $query = "SELECT image FROM user WHERE userId = $user_id";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $existingImage = $row['image'];
    } else {
        echo "<script>alert('User not found!'); window.location='profile.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // Handle Image Upload
            if (!empty($_FILES['userImage']['name'])) {
                // Delete the old image if a new one is uploaded
                if (file_exists("uploads/" . $existingImage)) {
                    unlink("uploads/" . $existingImage); // Delete the existing image from the server
                }

                $imageName = $_FILES['userImage']['name'];
                $imageTmpName = $_FILES['userImage']['tmp_name'];
                $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
                if (in_array($imageExt, $allowedExtensions)) {
                    $newImageName = uniqid('IMG-', true) . '.' . $imageExt;
                    move_uploaded_file($imageTmpName, "uploads/" . $newImageName);
                } else {
                    echo "<script>alert('Invalid file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
                    exit();
                }
            } else {
                $newImageName = $existingImage; // Keep old image if no new file uploaded
            }

            // Update database record
            $sql = "UPDATE user SET  
                        image = '$newImageName' 
                    WHERE userId = $user_id";

            if (mysqli_query($con, $sql)) {
                echo "<script>
                    alert('User updated successfully!');
                    window.location = 'profile.php';
                </script>";
            } else {
                echo "Error: " . mysqli_error($con);
            }
        // }
    // }
}
?>

<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Update User</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">

            <div class="form-row">
                <div class="col-md-6 mb-3">
                    <label for="userImage" class="font-weight-bold">User Image</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control mr-2" id="userImage" name="userImage">
                        <img src="uploads/<?= $existingImage ?>" alt="User Image" width="80">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary" type="submit" name="update">Update Profile</button>
                <a href="profile.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
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
    $('input[name="cnic"]').inputmask({
        mask: '99999-9999999-9',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
});
</script>

<?php include './includes/footer.php'; ?>
