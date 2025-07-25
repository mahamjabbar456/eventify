<?php
include 'connect.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $query = "SELECT * FROM user WHERE userId = $user_id";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $name = $row['username'];
        $email = $row['email'];
        $password = $row['password'];
        $cnic = $row['cnic'];
        $dateOfBirth = $row['dateOfBirth'];
        $address = $row['address'];
        $role = $row['roleId'];
        $phoneNo = $row['phone'];
        $existingImage = $row['image'];
    } else {
        echo "<script>alert('User not found!'); window.location='user.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $name = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    // $password = mysqli_real_escape_string($con, $_POST['password']);
    $cnic = mysqli_real_escape_string($con, str_replace('-', '', $_POST['cnic']));
    $dateOfBirth = mysqli_real_escape_string($con, $_POST['dateOfBirth']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $phoneNo = mysqli_real_escape_string($con, str_replace('-', '', $_POST['phoneNo']));

    // Check if email already exists for another user
    $emailCheckQuery = "SELECT userId FROM user WHERE email = '$email' AND userId != $user_id";
    $emailCheckResult = mysqli_query($con, $emailCheckQuery);
    
    if (mysqli_num_rows($emailCheckResult) > 0) {
        echo "<script>alert('Error: This email is already registered to another user.');</script>";
    } 
    // Check if CNIC already exists for another user
    else if (!empty($cnic)) {
        $cnicCheckQuery = "SELECT userId FROM user WHERE cnic = '$cnic' AND userId != $user_id";
        $cnicCheckResult = mysqli_query($con, $cnicCheckQuery);
        
        if (mysqli_num_rows($cnicCheckResult) > 0) {
            echo "<script>alert('Error: This CNIC is already registered to another user.');</script>";
        }
        else {
            // Proceed with image upload and update if checks pass
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
                        username = '$name', 
                        email = '$email', 
                        password = '$password', 
                        phone = '$phoneNo', 
                        address = '$address', 
                        dateOfBirth = '$dateOfBirth', 
                        roleId = '$role', 
                        cnic = '$cnic', 
                        image = '$newImageName' 
                    WHERE userId = $user_id";

            if (mysqli_query($con, $sql)) {
                echo "<script>
                    alert('User updated successfully!');
                    window.location = 'user.php';
                </script>";
            } else {
                echo "Error: " . mysqli_error($con);
            }
        }
    }
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
                <div class="col-md-4 mb-3">
                    <label for="username" class="font-weight-bold">User Name</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= $name ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="email" class="font-weight-bold">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $email ?>" required readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="cnic" class="font-weight-bold">CNIC</label>
                    <input type="text" class="form-control" id="cnic" name="cnic" value="<?= $cnic ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="dateOfBirth" class="font-weight-bold">Date Of Birth</label>
                    <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" value="<?= $dateOfBirth ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="role" class="font-weight-bold">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <?php
                            // Fetching roles
                            $rolesQuery = "SELECT * FROM role";
                            $rolesResult = mysqli_query($con, $rolesQuery);
                            while ($roleRow = mysqli_fetch_assoc($rolesResult)) {
                                $selected = ($roleRow['roleId'] == $row['roleId']) ? "selected" : "";
                                echo "<option value='{$roleRow['roleId']}' $selected>{$roleRow['roleName']}</option>";
                                // echo "<option value='{$roleRow['roleId']}' $selected>{$selected}</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="phoneNo" class="font-weight-bold">Phone No</label>
                    <input type="tel" class="form-control" id="phoneNo" name="phoneNo" value="<?= $phoneNo ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="userImage" class="font-weight-bold">User Image</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control mr-2" id="userImage" name="userImage">
                        <img src="uploads/<?= $existingImage ?>" alt="User Image" width="80">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="address" class="font-weight-bold">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= $address ?>" required>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary" type="submit" name="update">Update User</button>
                <a href="user.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
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
