<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../phpmail/src/Exception.php';
require_once '../phpmail/src/PHPMailer.php';
require_once '../phpmail/src/SMTP.php';
include 'connect.php';
session_start();

date_default_timezone_set('Asia/Karachi');
?>
<?php include './includes/header.php' ?>

<?php
if (!isset($_GET['token']) && !isset($_SESSION['reset_user_id'])) {
    header("Location: login.php");
    exit();
}
// Handle token verification
if(isset($_GET['token']) && isset($_GET['type']) && $_GET['type'] === 'reset') {
    $token = mysqli_real_escape_string($con, $_GET['token']);
    $currentTime = date('Y-m-d H:i:s');
    
    $sql = "SELECT userId, tokenExpiry, NOW() as dbTime FROM user 
            WHERE verificationToken = '$token'";
    
    $result = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if(strtotime($user['tokenExpiry']) > time()) {
            $_SESSION['reset_user_id'] = $user['userId'];
        } else {
            echo "<script>alert('The reset link has expired.'); window.location.href='login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('The reset link is invalid.'); window.location.href='login.php';</script>";
        exit();
    }
}

// Handle password reset form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['reset_user_id'])) {
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);
    
    if(empty($newPassword) || strlen($newPassword) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.');</script>";
    } elseif($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateSql = "UPDATE user 
                      SET password = '$hashedPassword', 
                          verificationToken = NULL, 
                          tokenExpiry = NULL 
                      WHERE userId = {$_SESSION['reset_user_id']}";
        
        if(mysqli_query($con, $updateSql)) {
            unset($_SESSION['reset_user_id']);
            echo "<script>alert('Your password has been reset successfully.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Failed to reset password. Please try again.');</script>";
        }
    }
}
?>
  
<div class="loginForm">
    <div class="form-box login">
        <?php if(isset($_GET['token']) || isset($_SESSION['reset_user_id'])): ?>
        <h2>Reset Password</h2>
        <form action="" method="POST" autocomplete="off">
            <div class="input-box">
                <input type="password" required id="newPassword" name="newPassword">
                <label for="password">New Password</label>
                <!-- <i class="bi bi-lock-fill togglePassword"></i> -->
                <ion-icon name="eye" class="togglePassword"></ion-icon>
            </div>
            <div class="input-box">
                <input type="password" required id="confirmPassword" name="confirmPassword">
                <label for="password">Confirm Password</label>
                <!-- <i class="bi bi-lock-fill togglePassword"></i> -->
                <ion-icon name="eye" class="togglePassword"></ion-icon>
            </div>
            <div class="input-box">
                <button class="btn1" type="submit">Change Password</button>
            </div>
        </form>
    <?php endif; ?>
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