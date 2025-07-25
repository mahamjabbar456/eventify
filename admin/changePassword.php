<?php include './includes/header.php' ?>
<?php include './includes/sidebar.php' ?>
<?php
include 'connect.php';

if (!isset($_SESSION['userId'])) {
    echo "<script>alert('Please login first'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['form_data'] = $_POST;
    $userId = $_SESSION['userId'];
    $currentPassword = mysqli_real_escape_string($con, $_POST['currentPassword']);
    $newPassword = mysqli_real_escape_string($con, $_POST['newPassword']);
    $confirmPassword = mysqli_real_escape_string($con, $_POST['confirmPassword']);

    // Validate inputs
    if (empty($currentPassword)) {
        echo "<script>alert('Current password is required'); window.location.href='changePassword.php';</script>";
        exit();
    }

    if (empty($newPassword) || empty($confirmPassword)) {
        echo "<script>alert('New password and confirmation are required'); window.location.href='changePassword.php';</script>";
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('New passwords do not match'); window.location.href='changePassword.php';</script>";
        exit();
    }

    if (strlen($newPassword) < 8) {
        echo "<script>alert('Password must be at least 8 characters long'); window.location.href='changePassword.php';</script>";
        exit();
    }

    // Check current password
    $result = mysqli_query($con, "SELECT password FROM user WHERE userId = '$userId'");
    if (!$result) {
        echo "<script>alert('Database error'); window.location.href='changePassword.php';</script>";
        exit();
    }
    
    $user = mysqli_fetch_assoc($result);
    if (!$user) {
        echo "<script>alert('User not found'); window.location.href='login.php';</script>";
        exit();
    }

    if (!password_verify($currentPassword, $user['password'])) {
        echo "<script>alert('Current password is incorrect'); window.location.href='changePassword.php';</script>";
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password in database
    $sql = "UPDATE user SET password = '$hashedPassword' WHERE userId = '$userId'";
    
    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Password changed successfully'); window.location.href='profile.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating password'); window.location.href='changePassword.php';</script>";
        exit();
    }
}
?>
<div class="main" style="overflow-x:hidden;">
    <!-- Top navigation bar -->
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart">
        <div class="loginForm">
        <div class="form-box login">
        <h2>Change Password</h2>
            <form method="POST">
                <div class="input-box">
                    <input type="password" required id="currentPassword" name="currentPassword" value="<?php echo isset($_SESSION['form_data']['currentPassword']) ? $_SESSION['form_data']['currentPassword'] : ''; ?>">
                    <label for="currentPassword">Old Password</label>
                    <!-- <i class="bi bi-lock-fill togglePassword"></i> -->
                     <ion-icon name="eye" class="togglePassword"></ion-icon>
                </div>
                <div class="input-box">
                    <input type="password" required id="newPassword" name="newPassword" value="<?php echo isset($_SESSION['form_data']['newPassword']) ? $_SESSION['form_data']['newPassword'] : ''; ?>">
                    <label for="password">New Password</label>
                    <!-- <i class="bi bi-lock-fill togglePassword"></i> -->
                    <ion-icon name="eye" class="togglePassword"></ion-icon>
                </div>
                <div class="input-box">
                    <input type="password" required id="confirmPassword" name="confirmPassword" value="<?php echo isset($_SESSION['form_data']['confirmPassword']) ? $_SESSION['form_data']['confirmPassword'] : ''; ?>">
                    <label for="password">Confirm Password</label>
                    <!-- <i class="bi bi-lock-fill togglePassword"></i> -->
                    <ion-icon name="eye" class="togglePassword"></ion-icon>
                </div>
                <div class="input-box">
                    <button class="btn1" type="submit">Change Password</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.togglePassword');
        
        toggleButtons.forEach((button) => {
            button.addEventListener("click", function() {
                const passwordInput = this.previousElementSibling.previousElementSibling;
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                
                const iconName = this.getAttribute('name') === 'eye' ? 'eye-off' : 'eye';
                this.setAttribute('name', iconName);
            });
        });
    });
</script>
<?php include './includes/footer.php' ?>