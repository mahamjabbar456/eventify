<?php include'./include/startingSection.php'; ?>
<link rel="stylesheet" href="assets/css/login.css">
<link rel="stylesheet" href="assets/css/profile.css"> <!-- Add a profile CSS if needed -->
</head>
<body>

<?php
session_start();
include 'admin/connect.php';

// Check if user is logged in
if(!isset($_SESSION['customerId'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get user data from database
$customer_id = $_SESSION['customerId'];
$query = "SELECT * FROM customer WHERE customerId = '$customer_id'";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);

?>
<!--header section starts-->
<?php include'./include/header.php'; ?>
<!--header section ends-->

<div class="clientPadding">
    <div class="profile-container">
        <h2>Your Profile</h2>
        <div class="profile-info">
            <div class="profile-field">
                <span class="field-label">Username:</span>
                <span class="field-value"><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            <div class="profile-field">
                <span class="field-label">Email:</span>
                <span class="field-value"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="profile-field">
                <span class="field-label">Phone Number:</span>
                <span class="field-value"><?php echo htmlspecialchars($user['phoneNo']); ?></span>
            </div>
            <div class="profile-field">
                <span class="field-label">Address:</span>
                <span class="field-value"><?php echo htmlspecialchars($user['address']); ?></span>
            </div>
        </div>
        <a href="editProfile.php" class="edit-profile-btn">Edit Profile</a>
    </div>
</div>

<!-- footer section starts -->
<?php include'./include/footer.php'; ?>
<!-- footer section ends -->

<!-- theme toggler starts-->
<?php include'./include/themeToggler.php'; ?>
<!-- theme toggler ends -->

<?php include'./include/scriptSection.php'; ?>