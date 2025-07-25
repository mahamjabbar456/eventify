<?php
session_start();
include 'admin/connect.php';

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if (!isset($_SESSION['customerId'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerId = $_SESSION['customerId'];
    $currentPassword = mysqli_real_escape_string($con, $_POST['currentPassword']);
    $newPassword = mysqli_real_escape_string($con, $_POST['newPassword']);
    $confirmPassword = mysqli_real_escape_string($con, $_POST['confirmPassword']);

    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: changePassword.php');
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "New passwords do not match";
        header('Location: changePassword.php');
        exit();
    }

    if (strlen($newPassword) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header('Location: changePassword.php');
        exit();
    }

    // Check current password
    $result = mysqli_query($con, "SELECT password FROM customer WHERE customerId = '$customerId'");
    if (!$result) {
        // Handle query error
        $_SESSION['error'] = "Database error: " . mysqli_error($con);
        header('Location: changePassword.php');
        exit();
    }
    
    $customer = mysqli_fetch_assoc($result);
    if (!$customer) {
        // Handle case where customer not found
        $_SESSION['error'] = "Customer not found";
        header('Location: changePassword.php');
        exit();
    }

    if (!password_verify($currentPassword, $customer['password'])) {
        $_SESSION['error'] = "Current password is incorrect";
        header('Location: changePassword.php');
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password in database
    // $updateStmt = $conn->prepare("UPDATE customers SET password = ? WHERE customer_id = ?");
    // $updateStmt->bind_param("si", $hashedPassword, $customerId);
    $sql = "UPDATE customer SET password = '$hashedPassword' WHERE customerId = '$customerId'";
    
    if (mysqli_query($con,$sql)) {
        $_SESSION['success'] = "Password changed successfully";
        header('Location: index.php'); // Redirect to profile page
        exit();
    } else {
        $_SESSION['error'] = "Error updating password";
        header('Location: changePassword.php');
        exit();
    }
}
?>

<?php include'./include/startingSection.php'; ?>
    <link rel="stylesheet" href="assets/css/changePassword.css" />
  </head>
  <body>
  
  <!--header section starts-->

  <?php include'./include/header.php'; ?>

  <!--header section ends-->

  <div class="main">
     <h1 class="heading"><span>Change</span> password</h1>
     <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
        <form action="" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="currentPassword">Current Password</label>
            <div class="input-box">
                <input type="password" name="currentPassword" id="currentPassword" placeholder="Current Password" required class="password">
                <i class="fas fa-lock togglePassword"></i>
            </div>
        </div>
        <div class="form-group">
            <label for="newPassword">New Password</label>
            <div class="input-box">
                <input type="password" name="newPassword" id="newPassword" minlength="8" placeholder="New Password" required class="password">
                <i class="fas fa-lock togglePassword"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <div class="input-box">
                <input type="password" name="confirmPassword" id="confirmPassword" minlength="8" placeholder="Confirm Password" required class="password">
                <i class="fas fa-lock togglePassword"></i>
            </div>
        </div>
        <input type="submit" value="Change Password" class="btn">
        </form>
  </div>

    <!-- footer section starts -->

    <?php include'./include/footer.php'; ?>

    <!-- footer section ends -->

    <!-- theme toggler starts-->

    <?php include'./include/themeToggler.php'; ?>

    <!-- theme toggler ends -->

    <script>
        const togglePasswordButtons = document.querySelectorAll('.togglePassword');
        const passwordInputs = document.querySelectorAll('.password');
        togglePasswordButtons.forEach((button, index) => {
            button.addEventListener("click", function() {
                const passwordInput = passwordInputs[index];
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
    <?php include'./include/scriptSection.php'; ?>