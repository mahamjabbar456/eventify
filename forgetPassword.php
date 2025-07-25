<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once 'phpmail/src/Exception.php';
require_once 'phpmail/src/PHPMailer.php';
require_once 'phpmail/src/SMTP.php';
include 'admin/connect.php';
session_start();
function showSweetAlert($icon, $title, $text, $redirect = null) {
    echo "<script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$text',
            showConfirmButton: true
        })";
    if ($redirect) {
        echo ".then(() => { window.location.href = '$redirect'; })";
    }
    echo ";</script>";
}
date_default_timezone_set('Asia/Karachi');
?>

<?php include'./include/startingSection.php'; ?>
<link rel="stylesheet" href="assets/css/changePassword.css" />
</head>
<body>

<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($con, trim($_POST['currentEmail']));
    
    // Check if email exists
    $sql = "SELECT customerId, name, isVerified FROM customer WHERE email = '$email'";
    $result = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if($user['isVerified'] == 0) {
            showSweetAlert('error', 'Account Not Verified', 'Please verify your email first.', 'login.php');
        } else {
            // Generate reset token (reusing verificationToken field)
            $resetToken = bin2hex(random_bytes(32));
            $tokenExpiry = date("Y-m-d H:i:s", strtotime('+1 hour'));
            
            // Store token in database
            $updateSql = "UPDATE customer 
                          SET verificationToken = '$resetToken', 
                              tokenExpiry = '$tokenExpiry' 
                          WHERE customerId = {$user['customerId']}";
            
            if(mysqli_query($con, $updateSql)) {
                // Send reset email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = "";
                    $mail->Password = "";
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    
                    $mail->setFrom("eventifywebsite012@gmail.com", "Eventify");
                    $mail->addAddress($email, $user['name']);
                    
                    $mail->isHTML(true);
                    $mail->Subject = "Password Reset Request";
                    
                    $resetUrl = "http://localhost/eventplannersystem/resetPassword.php?token=$resetToken&type=reset";
                    
                    $mail->Body = "
                        <h2>Password Reset Request</h2>
                        <p>Click the link below to reset your password:</p>
                        <p><a href='$resetUrl'>Reset Password</a></p>
                        <p>This link will expire in 1 hour.</p>
                        <p>If you didn't request this, please ignore this email.</p>
                    ";
                    
                    $mail->send();
                    showSweetAlert('success', 'Email Sent', 'Password reset link has been sent to your email.', 'login.php');
                } catch (Exception $e) {
                    showSweetAlert('error', 'Email Error', 'Could not send reset email. Error: ' . $e->getMessage());
                }
            } else {
                showSweetAlert('error', 'Database Error', mysqli_error($con));
            }
        }
    } else {
        showSweetAlert('error', 'Email Not Found', 'No account found with this email address.');
    }
}
?>

<!--header section starts-->
<?php include'./include/header.php'; ?>
<!--header section ends-->

<div class="main">
    <h1 class="heading"><span>Forgot</span> Password</h1>
    <form action="" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="currentEmail">Registered Email</label>
            <div class="input-box">
                <input type="email" name="currentEmail" id="currentEmail" placeholder="Your registered email" required>
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <input type="submit" value="Send Reset Link" class="btn">
    </form>
</div>

<!-- footer section starts -->
<?php include'./include/footer.php'; ?>
<!-- footer section ends -->

<!-- theme toggler starts-->
<?php include'./include/themeToggler.php'; ?>
<!-- theme toggler ends -->

<?php include'./include/scriptSection.php'; ?>
