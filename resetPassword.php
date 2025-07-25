<?php
include 'admin/connect.php';
session_start();
date_default_timezone_set('Asia/Karachi');

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

<?php
// Handle token verification
if(isset($_GET['token']) && isset($_GET['type']) && $_GET['type'] === 'reset') {
    $token = mysqli_real_escape_string($con, $_GET['token']);
    $currentTime = date('Y-m-d H:i:s'); // Get current time from PHP
    
     $sql = "SELECT customerId, tokenExpiry, NOW() as dbTime FROM customer 
            WHERE verificationToken = '$token'";
    
    $result = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Debug output
        error_log("Current Time: $currentTime");
        error_log("Token Expiry: " . $user['tokenExpiry']);
        
        if(strtotime($user['tokenExpiry']) > time()) {
            $_SESSION['reset_user_id'] = $user['customerId'];
        } else {
            showSweetAlert('error', 'Invalid Token', 'The reset link has expired.', 'login.php');
            exit();
        }
    } else {
        showSweetAlert('error', 'Invalid Token', 'The reset link is invalid.', 'login.php');
        exit();
    }
}

// Handle password reset form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['reset_user_id'])) {
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);
    
    if(empty($newPassword) || strlen($newPassword) < 8) {
        showSweetAlert('error', 'Error', 'Password must be at least 8 characters long.');
    } elseif($newPassword !== $confirmPassword) {
        showSweetAlert('error', 'Error', 'Passwords do not match.');
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateSql = "UPDATE customer 
                      SET password = '$hashedPassword', 
                          verificationToken = NULL, 
                          tokenExpiry = NULL 
                      WHERE customerId = {$_SESSION['reset_user_id']}";
        
        if(mysqli_query($con, $updateSql)) {
            unset($_SESSION['reset_user_id']);
            showSweetAlert('success', 'Success', 'Your password has been reset successfully.', 'login.php');
        } else {
            showSweetAlert('error', 'Error', 'Failed to reset password. Please try again.');
        }
    }
}
?>
  
  <!--header section starts-->

  <?php include'./include/header.php'; ?>

  <!--header section ends-->

  <div class="main">
    <h1 class="heading"><span>Reset</span> Password</h1>
    <?php if(isset($_GET['token']) || isset($_SESSION['reset_user_id'])): ?>
    <form action="" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="newPassword">New Password</label>
            <div class="input-box">
                <input type="password" name="newPassword" id="newPassword" placeholder="New Password" required minlength="8">
                <i class="fas fa-lock togglePassword"></i>
            </div>
        </div>
        <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <div class="input-box">
                <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required minlength="8">
                <i class="fas fa-lock togglePassword"></i>
            </div>
        </div>
        <input type="submit" value="Reset Password" class="btn">
    </form>
    <?php endif; ?>
</div>

    <!-- footer section starts -->

    <?php include'./include/footer.php'; ?>

    <!-- footer section ends -->

    <!-- theme toggler starts-->

    <?php include'./include/themeToggler.php'; ?>

    <!-- theme toggler ends -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const togglePasswordButtons = document.querySelectorAll('.togglePassword');
        
        togglePasswordButtons.forEach((button) => {
            button.addEventListener("click", function() {
                // Find the nearest input sibling
                const input = this.parentElement.querySelector('input[type="password"], input[type="text"]');
                
                if (input) {
                    // Toggle input type
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Toggle eye icon
                    this.classList.toggle('fa-lock');
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                }
            });
        });
    });
    </script>
    <?php include'./include/scriptSection.php'; ?>