<?php include'./include/startingSection.php'; ?>
  <link rel="stylesheet" href="assets/css/registerHall.css" />
  </head>
  <body>

    <?php
        session_start();
        include 'admin/connect.php';
        if (!isset($_SESSION['customerId'])) {
            header('Location: login.php');
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
            // Get form data
            $_SESSION['form_data'] = $_POST;
            $_SESSION['form_data']['logo_tmp'] = $_FILES['logo']['tmp_name'];
            $_SESSION['form_data']['logo_name'] = $_FILES['logo']['name'];
            $_SESSION['form_data']['cover_tmp'] = $_FILES['cover']['tmp_name'];
            $_SESSION['form_data']['cover_name'] = $_FILES['cover']['name'];
            $phoneNo = trim($_POST['phoneNo']);
            // Remove all non-numeric characters from CNIC
            $phoneNo = preg_replace('/[^0-9]/', '', $phoneNo);
            $hallName = mysqli_real_escape_string($con, $_POST['hallName']);
            $address =  mysqli_real_escape_string($con, $_POST['address']);
            $phoneNo = mysqli_real_escape_string($con, $phoneNo);
            $capacity = mysqli_real_escape_string($con, $_POST['capacity']);
            $event_capacity = mysqli_real_escape_string($con, $_POST['event_capacity']);
            $status = "pending" ;
            $hallDetail = mysqli_real_escape_string($con, $_POST['hallDetail']);

            $checkQuery = "SELECT * FROM hall WHERE name = '$hallName'";
            $checkResult = mysqli_query($con,$checkQuery);

            if(mysqli_num_rows($checkResult) > 0){
                showSweetAlert('warning', 'Registration Failed', 'Hall name already exists! Please choose a different name.');
            } else {
                // Image upload handling
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // Logo Image
            $logoImage = $_FILES['logo']['name'];
            $logoTmpName = $_FILES['logo']['tmp_name'];
            $logoSize = $_FILES['logo']['size'];
            $logoError = $_FILES['logo']['error'];
            $logoExt = strtolower(pathinfo($logoImage, PATHINFO_EXTENSION));

            // Cover Image
            $coverImage = $_FILES['cover']['name'];
            $coverTmpName = $_FILES['cover']['tmp_name'];
            $coverSize = $_FILES['cover']['size'];
            $coverError = $_FILES['cover']['error'];
            $coverExt = strtolower(pathinfo($coverImage, PATHINFO_EXTENSION));

            // Validate file types
            if (in_array($logoExt, $allowedExtensions) && in_array($coverExt, $allowedExtensions)) {
                // Check if there is an error in file upload
                if ($logoError === 0 || $coverError === 0) {
                    // Check file size (5MB limit)
                    if ($logoSize < 5000000 || $coverSize < 5000000) {
                        // Unique names for images
                        $newLogoName = uniqid('LOGO-', true) . '.' . $logoExt;
                        $newCoverName = uniqid('COVER-', true) . '.' . $coverExt;

                        // Set image upload path
                        $logoDestination = "admin/uploads/" . $newLogoName;
                        $coverDestination = "admin/uploads/" . $newCoverName;

                        // Move images to the destination folder
                        move_uploaded_file($logoTmpName, $logoDestination);
                        move_uploaded_file($coverTmpName, $coverDestination);

                        // Insert into database
                        $sql = "INSERT INTO hall (name, address, capacity, contactNo, detail, logo, cover, event_capacity, status) 
                                VALUES ('$hallName', '$address', '$capacity', '$phoneNo', '$hallDetail', '$newLogoName', '$newCoverName', '$event_capacity', '$status')";

                        if (mysqli_query($con, $sql)) {
                            unset($_SESSION['form_data']);
                            showSweetAlert('success', 'Registration Successful', 'Hall added successfully!',  $_SERVER['HTTP_REFERER']);
                        } else {
                            echo "Error: " . mysqli_error($con);
                        }
                    } else {
                        showSweetAlert('error', 'File Requirements', 'File size too large! Max 5MB allowed.');
                    }
                } else {
                    showSweetAlert('error', 'File Requirements', 'Error uploading file!');
                }
            } else {
                showSweetAlert('error', 'File Requirements', 'Invalid file type! Only JPG, PNG, GIF, and WEBP allowed.');
            }
            unset($_SESSION['form_data']);
            }
        }
    ?>

    <!--header section starts-->

    <?php 
    session_start();
    include'./include/header.php'; ?>

    <div class="registerHallForm">

    <h1 class="heading"> Register <span>Hall</span> </h1>
    <div class="contact">
        <form action="" method="POST" enctype="multipart/form-data" id="bookingForm" autocomplete="off">
            <div class="inputBox">
                <div class="form-group">
                    <label for="hallName">Hall Name</label>
                    <input type="text" id="hallName" class="form-control" placeholder="Enter your hall name" name="hallName" required value="<?php echo isset($_POST['hallName']) ? $_POST['hallName'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" class="form-control" placeholder="Enter your address" name="address" required value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phoneNo">Phone No</label>
                    <input type="tel" class="form-control" id="phoneNo" placeholder="Enter your Phone No" name="phoneNo" required value="<?php echo isset($_POST['phoneNo']) ? $_POST['phoneNo'] : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="capacity" class="font-weight-bold">Capacity</label>
                <input type="number" class="form-control" id="capacity" placeholder="Enter your hall capacity"
                name="capacity" required value="<?php echo isset($_POST['capacity']) ? $_POST['capacity'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="event_capacity">Function Capacity at a time</label>
                <input type="number" class="form-control" id="event_capacity" placeholder="Enter your Maximum function capacity at a time:" name="event_capacity" required value="<?php echo isset($_POST['event_capacity']) ? $_POST['event_capacity'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="logo">Logo Image</label>
                <input type="file" class="form-control" id="logo" placeholder="Enter your Image" name="logo" required>
                <?php if (isset($_SESSION['form_data']['logo_name'])): ?>
                    <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['logo_name']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="cover">Cover Image</label>
                <input type="file" class="form-control" id="cover" placeholder="Enter your Image" name="cover" required>
                <?php if (isset($_SESSION['form_data']['cover_name'])): ?>
                    <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['cover_name']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="hallDetail">Detail</label>
                <textarea name="hallDetail" id="hallDetail" placeholder="Enter your description" rows="3" class="form-control"><?php echo isset($_POST['hallDetail']) ? $_POST['hallDetail'] : ''; ?></textarea>
            </div>

            <button type="submit" name="register" class="btn btn-primary">Register Now</button> 
        </form>
  
    </div>
    </div>

<!-- footer section starts -->

<?php include'./include/footer.php'; ?>

<!-- footer section ends -->

<!-- theme toggler starts-->

<?php include'./include/themeToggler.php'; ?>

<!-- theme toggler ends -->

<script>
    $(document).ready(function() {
    // Phone number masking
    $('input[name="phoneNo"]').inputmask({
        mask: '0399-9999999',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
});
</script>

<?php include'./include/scriptSection.php'; ?>