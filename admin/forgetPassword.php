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
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($con, trim($_POST['currentEmail']));
    
    // Check if email exists
    $sql = "SELECT userId, username FROM user WHERE email = '$email'";
    $result = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate reset token (reusing verificationToken field)
        $resetToken = bin2hex(random_bytes(32));
        $tokenExpiry = date("Y-m-d H:i:s", strtotime('+1 hour'));
        
        // Store token in database
        $updateSql = "UPDATE user 
                      SET verificationToken = '$resetToken', 
                          tokenExpiry = '$tokenExpiry' 
                      WHERE userId = {$user['userId']}";
        
        if(mysqli_query($con, $updateSql)) {
            // Send reset email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = "eventifywebsite012@gmail.com";
                $mail->Password = "bfni fpwv rbdl dmkc";
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                $mail->setFrom("eventifywebsite012@gmail.com", "Eventify");
                $mail->addAddress($email, $user['username']);
                
                $mail->isHTML(true);
                $mail->Subject = "Password Reset Request";
                
                $resetUrl = "http://localhost/eventplannersystem/admin/resetPassword.php?token=$resetToken&type=reset";
                
                $mail->Body = "
                    <h2>Password Reset Request</h2>
                    <p>Click the link below to reset your password:</p>
                    <p><a href='$resetUrl'>Reset Password</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                ";
                
                $mail->send();
                echo "<script>alert('Password reset link has been sent to your email.'); window.location.href='login.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Could not send reset email. Error: " . addslashes($e->getMessage()) . "');</script>";
            }
        } else {
            echo "<script>alert('Database Error: " . addslashes(mysqli_error($con)) . "');</script>";
        }
    } else {
        echo "<script>alert('No account found with this email address.');</script>";
    }
}
?>
<div class="loginForm">
    <div class="form-box login">
        <h2>Forget Password</h2>
        <form action="" method="POST">
            <div class="input-box">
                <input id="currentEmail" name="currentEmail" type="email" required>
                <label for="currentEmail">Email</label>
                <ion-icon name="mail"></ion-icon>
            </div>
            <div class="input-box">
                <button class="btn1" type="submit">Send Email</button>
            </div>
        </form>
    </div>
</div>
</div>

<?php include './includes/footer.php' ?>