<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once  'phpmail/src/Exception.php';
require_once  'phpmail/src/PHPMailer.php';
require_once  'phpmail/src/SMTP.php';

?>
<?php include'./include/startingSection.php'; ?>
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<?php
session_start();
include 'admin/connect.php';

function showSweetAlert($icon, $title, $text, $redirect = null) {
    echo "<script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$text',
            showConfirmButton: true,
            timer: 3000
        })";
    if ($redirect) {
        echo ".then(() => { window.location.href = '$redirect'; })";
    }
    echo "</script>";
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['register'])) {
        // Get and sanitize inputs
        $name = mysqli_real_escape_string($con, trim($_POST['userName']));
        $phone = trim($_POST['phoneNo']);
        $address = mysqli_real_escape_string($con, trim($_POST['address']));
        $email = mysqli_real_escape_string($con, trim($_POST['email']));
        $password = trim($_POST['password']);
        
        // Validate inputs
        $errors = [];
        
        // Name validation
        if(empty($name)) {
            $errors[] = "Name is required";
        } elseif(strlen($name) < 3) {
            $errors[] = "Name must be at least 3 characters";
        }
        
        // Phone validation
        if(empty($phone)) {
            $errors[] = "Phone number is required";
        } else {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            $phone = mysqli_real_escape_string($con, $phone);
        }
        
        // Address validation
        if(empty($address)) {
            $errors[] = "Address is required";
        }
        
        // Email validation
        if(empty($email)) {
            $errors[] = "Email is required";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        // Password validation
        if(empty($password)) {
            $errors[] = "Password is required";
        } elseif(strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        
        if(!empty($errors)) {
            showSweetAlert('error', 'Validation Error', implode('<br>', $errors));
        } else {
            // Check if email exists
            $checkQuery = "SELECT * FROM customer WHERE email = '$email'";
            $checkResult = mysqli_query($con, $checkQuery);
            
            if(mysqli_num_rows($checkResult) > 0) {
                showSweetAlert('warning', 'Registration Failed', 'This email is already registered. Please login.');
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                date_default_timezone_set('Asia/Karachi'); 
                
                // Generate verification token
                $verificationToken = bin2hex(random_bytes(32));
                $tokenExpiry = date("Y-m-d H:i:s", strtotime('+2 hours'));
                
                // Insert into database with verification data
                $sql = "INSERT INTO customer (name, phoneNo, address, email, password, verificationToken, tokenExpiry) 
                        VALUES('$name', '$phone', '$address', '$email', '$hashedPassword', '$verificationToken', '$tokenExpiry')";
                
                if(mysqli_query($con, $sql)) {
                    // Send verification email
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = "eventifywebsite012@gmail.com";
                        $mail->Password = "bfni fpwv rbdl dmkc";
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
                        $mail->Port = 587;
                        
                        $mail->setFrom("eventifywebsite012@gmail.com", "Eventify");
                        $mail->addAddress($email, $name);
                        
                        $mail->isHTML(true);
                        $mail->Subject = "Verify Your Email Address";
                        
                        $verificationUrl = "http://localhost/eventplannersystem/verify.php?token=$verificationToken";
                        
                        $mail->Body = "
                            <h2>Thank you for registering!</h2>
                            <p>Please click the link below to verify your email address:</p>
                            <p><a href='$verificationUrl'>Verify Email</a></p>
                            <p>This link will expire in 2 hours.</p>
                        ";
                        
                        $mail->send();
                        
                        showSweetAlert('success', 'Registration Successful', 'A verification link has been sent to your email. Please verify your account before logging in.', 'index.php');
                    } catch (Exception $e) {
                        // Delete the user if email fails to send
                         mysqli_query($con, "DELETE FROM customer WHERE email = '$email'");
                        showSweetAlert('error', 'Email Error', 'Could not send verification email. Error: ' . $mail->ErrorInfo);
                    }
                } else {
                    showSweetAlert('error', 'Database Error', mysqli_error($con));
                }
            }
        }
    } elseif(isset($_POST['login'])) {
        $email = mysqli_real_escape_string($con, trim($_POST['email']));
        $password = trim($_POST['password']);
        
        // Validate inputs
        $errors = [];
        
        if(empty($email)) {
            $errors[] = "Email is required";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if(empty($password)) {
            $errors[] = "Password is required";
        }
        
        if(!empty($errors)) {
            showSweetAlert('error', 'Validation Error', implode('<br>', $errors));
        } else {
            $sql = "SELECT customerId, name, email, password FROM customer WHERE email='$email'";
            $result = mysqli_query($con, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                if(password_verify($password, $user['password'])) {
                    // Check if email is verified
                    $verifiedCheck = mysqli_query($con, "SELECT isVerified FROM customer WHERE customerId = {$user['customerId']}");
                    $verifiedStatus = mysqli_fetch_assoc($verifiedCheck);
                    
                    if($verifiedStatus['isVerified'] == 0) {
                        showSweetAlert('warning', 'Account Not Verified', 'Please verify your email address before logging in. Check your inbox for the verification link.');
                    } else {
                        // Set session variables
                        $_SESSION['customerId'] = $user['customerId'];
                        $_SESSION['customerName'] = $user['name'];
                        $_SESSION['customerEmail'] = $user['email'];
                        
                        showSweetAlert('success', 'Login Successful', 'Welcome back, ' . $user['name'] . '!', 'index.php');
                    }
                } else {
                    showSweetAlert('error', 'Login Failed', 'Invalid email or password');
                }
            } else {
                showSweetAlert('error', 'Login Failed', 'Invalid email or password');
            }
        }
    }
}
?>

<!--header section starts-->
<?php include'./include/header.php'; ?>
<!--header section ends-->

<div class="container clientPadding">
    <div class="form-box login">
        <form action="" method="post" id="loginForm" autocomplete="off">
            <h1>Login</h1>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required class="password">
                <i class="fas fa-lock togglePassword"></i>
            </div>
            <div class="forgot-link">
                <a href="forgetPassword.php" class="signuplink">Forget Password?</a>
                <a href="resendVerification.php" class="signuplink">Resend Verification Email</a>
            </div>
            <button type="submit" name="login" class="btn">Login</button>
        </form>
    </div>

    <div class="form-box register">
        <form action="" method="post" id="registerForm" autocomplete="off">
            <h1>Registration</h1>
            <div class="input-box">
                <input type="text" name="userName" placeholder="Username" required minlength="3" >
                <i class="fas fa-user"></i>
            </div>
            <div class="input-box">
                <input type="tel" name="phoneNo" id="phoneNo" placeholder="Phone Number" required >
                <i class="fas fa-phone"></i>
            </div>
            <div class="input-box">
                <input type="text" name="address" placeholder="Address" required >
                <i class="fas fa-map-marker"></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required >
                <i class="fas fa-envelope"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" class="password" required minlength="8" >
                <i class="fas fa-lock togglePassword"></i>
            </div>
            <button type="submit" name="register" class="btn">Register</button>
        </form>
    </div>

    <div class="toggle-box">
        <div class="toggle-panel toggle-left">
            <h1>Hello, Welcome!</h1>
            <p>Don't have an account?</p>
            <button class="btn register-btn">Register</button>
        </div>
        <div class="toggle-panel toggle-right">
            <h1>Welcome Back!</h1>
            <p>Already have an account?</p>
            <button class="btn login-btn">Login</button>
        </div>
    </div>
</div>

<!-- footer section starts -->
<?php include'./include/footer.php'; ?>
<!-- footer section ends -->

<!-- theme toggler starts-->
<?php include'./include/themeToggler.php'; ?>
<!-- theme toggler ends -->

<script src="assets/js/login.js"></script>
<?php include'./include/scriptSection.php'; ?>