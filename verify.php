<?php
ob_start();
include 'admin/connect.php';
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <script src="assets/js/sweetalert2.all.min.js"></script>
</head>
<body>
    <?php
    function showSweetAlert($icon, $title, $text, $redirect = null) {
        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$text',
                showConfirmButton: true
            })";
        if ($redirect) {
            echo ".then((result) => { if (result.isConfirmed) { window.location.href = '$redirect'; } })";
        }
        echo ";</script>";
    }

    if(isset($_GET['token'])) {
        $token = mysqli_real_escape_string($con, $_GET['token']);
        
        // Debugging
        error_log("Verification attempt with token: $token");
        
        $sql = "SELECT customerId FROM customer 
                WHERE verificationToken = '$token' 
                AND isVerified = 0 
                AND tokenExpiry > NOW()";
        
        $result = mysqli_query($con, $sql);
        
        if(mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            $updateSql = "UPDATE customer 
                          SET isVerified = 1, 
                              verificationToken = NULL, 
                              tokenExpiry = NULL 
                          WHERE customerId = {$user['customerId']}";
            
            if(mysqli_query($con, $updateSql)) {
                showSweetAlert('success', 'Email Verified', 'Your email has been verified successfully! You can now login.', 'login.php');
            } else {
                showSweetAlert('error', 'Verification Failed', 'Database error. Please try again.', 'login.php');
            }
        } else {
            // Additional debug info
            $check = mysqli_query($con, "SELECT tokenExpiry, isVerified FROM customer WHERE verificationToken = '$token'");
            if(mysqli_num_rows($check) > 0) {
                $row = mysqli_fetch_assoc($check);
                error_log("Token exists but problem - Expiry: {$row['tokenExpiry']}, Verified: {$row['isVerified']}");
            }
            showSweetAlert('error', 'Invalid Token', 'The verification link is invalid or has expired (links expire after 2 hours).', 'login.php');
        }
    } else {
        header("Location: login.php");
        exit();
    }
    ?>
</body>
</html>
<?php ob_end_flush(); ?>