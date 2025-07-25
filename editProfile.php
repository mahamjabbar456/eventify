<?php include'./include/startingSection.php'; ?>
<link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>

<?php
session_start();
include 'admin/connect.php';

// Check if user is logged in
if(!isset($_SESSION['customerId'])) {
    header("Location: login.php");
    exit();
}


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

// Get current user data
$customer_id = $_SESSION['customerId'];
$query = "SELECT * FROM customer WHERE customerId = '$customer_id'";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // Get and sanitize inputs
    $name = mysqli_real_escape_string($con, trim($_POST['username']));
    $phone = trim($_POST['phoneNo']);
    $address = mysqli_real_escape_string($con, trim($_POST['address']));
    $email = $user['email']; // Use the original email from session
    
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
        if(strlen($phone) < 10) {
            $errors[] = "Phone number must be at least 10 digits";
        }
        $phone = mysqli_real_escape_string($con, $phone);
    }
    
    // Address validation
    if(empty($address)) {
        $errors[] = "Address is required";
    }
    
    if(empty($errors)) {
        // Update query
        $updateQuery = "UPDATE customer SET 
                        name = '$name',
                        phoneNo = '$phone',
                        address = '$address'
                        WHERE customerId = '$customer_id'";
        
        if(mysqli_query($con, $updateQuery)) {
            // Success message
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: 'Your profile has been updated successfully!',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'profile.php';
                });
            </script>";
        } else {
            // Error message
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: 'There was an error updating your profile. Please try again.'
                });
            </script>";
        }
    } else {
        // Show validation errors
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: '".implode('<br>', $errors)."'
            });
        </script>";
    }
}
?>

<!--header section starts-->
<?php include'./include/header.php'; ?>
<!--header section ends-->

<div class="clientPadding">
    <div class="profile-container">
        <h2>Edit Profile</h2>
        <form class="edit-profile-form" method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="phoneNo">Phone Number</label>
                <input type="tel" id="phoneNo" name="phoneNo" value="<?php echo htmlspecialchars($user['phoneNo']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">Save Changes</button>
                <a href="profile.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- footer section starts -->
<?php include'./include/footer.php'; ?>
<!-- footer section ends -->
<?php include'./include/themeToggler.php'; ?>

<script>
    $(document).ready(function() {
        // Phone number masking
        $('#phoneNo').inputmask({
            mask: '0399-9999999',
            placeholder: '_',
            showMaskOnHover: true,
            showMaskOnFocus: true,
        });
        
        // Prevent form resubmission on page refresh
        if(window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
</script>

<?php include'./include/scriptSection.php'; ?>