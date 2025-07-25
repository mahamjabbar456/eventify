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
?>
<?php include'./include/startingSection.php'; ?>
<link rel="stylesheet" href="assets/css/changePassword.css" />
</head>
<body>
<?php include'./include/header.php'; ?>

<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    
    // Check if email exists and is not verified
    $sql = "SELECT customerId, name, isVerified FROM customer WHERE email = '$email'";
    $result = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $name = $user['name'];
        
        if($user['isVerified'] == 1) {
            showSweetAlert('info', 'Already Verified', 'This email is already verified. Please login.', 'login.php');
        } else {
            date_default_timezone_set('Asia/Karachi'); 
            // Generate new token
            $newToken = bin2hex(random_bytes(32));
            $tokenExpiry = date("Y-m-d H:i:s", strtotime('+2 hours'));
            
            // Update in database
            $updateSql = "UPDATE customer 
                          SET verificationToken = '$newToken', 
                              tokenExpiry = '$tokenExpiry' 
                          WHERE customerId = {$user['customerId']}";
            
            if(mysqli_query($con, $updateSql)) {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = "eventifywebsite012@gmail.com";
                        $mail->Password = "bfni fpwv rbdl dmkc";
                        // $mail->SMTPSecure = "ssl";
                        // $mail->Port = 587;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
                        $mail->Port = 587;
                        
                        $mail->setFrom("eventifywebsite012@gmail.com", "Eventify");
                        $mail->addAddress($email, $name);
                        
                        $mail->isHTML(true);
                        $mail->Subject = "Verify Your Email Address";
                        
                        $verificationUrl = "http://localhost/eventplannersystem/verify.php?token=$newToken";
                        
                        $mail->Body = "
                        <h2>Email Verification</h2>
                        <p>Please click the link below to verify your email address:</p>
                        <p><a href='$verificationUrl'>Verify Email</a></p>
                        <p>This link will expire in 2 hours.</p>
                    ";
                    
                    $mail->send();
                    
                    showSweetAlert('success', 'Email Sent', 'A new verification link has been sent to your email.', 'login.php');
                } catch (Exception $e) {
                    showSweetAlert('error', 'Email Error', 'Could not send verification email. Please try again later.');
                }
            } else {
                showSweetAlert('error', 'Database Error', 'Could not update verification details. Please try again.');
            }
        }
    } else {
        showSweetAlert('error', 'Email Not Found', 'This email is not registered with us.');
    }
}
?>

<!-- HTML form for resend verification -->
 <div class="main">
     <h1 class="heading"><span>Resend</span> Verification Email</h1>
        <form action="" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="currentEmail">Current Email</label>
            <div class="input-box">
                <input type="email" name="email" id="currentEmail" placeholder="Current Email" required>
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <button type="submit" class="btn">Resend Verification</button>
        </form>
  </div>
<?php include'./include/footer.php'; ?>
<!-- footer section ends -->

<!-- theme toggler starts-->
<?php include'./include/themeToggler.php'; ?>
<!-- theme toggler ends -->

<script src="assets/js/login.js"></script>
<?php include'./include/scriptSection.php'; ?>