<?php
    session_start();
    include 'admin/connect.php';
    if (!isset($_SESSION['customerId'])) {
        header('Location: login.php');
        exit();
    }
    $customerId = $_SESSION['customerId'];

    $safeCustomerId = mysqli_real_escape_string($con, $customerId);

    $countResult = mysqli_query($con, 
    "SELECT COUNT(*) as total_bookings,
    SUM(status = 'confirmed') as confirmed_bookings,
    SUM(status = 'pending') as pending_bookings,
    SUM(status = 'cancelled') as cancelled_bookings
    FROM booking WHERE customerId = '$safeCustomerId'");

    $countData = mysqli_fetch_assoc($countResult);

    $bookingResult = mysqli_query($con, 
                "SELECT b.*, p.packageName 
                FROM booking b
                LEFT JOIN package p ON b.packageId = p.packageId
                WHERE b.customerId = $safeCustomerId
                ORDER BY b.dateOfBooking DESC");

?>

<?php include'./include/startingSection.php'; ?>
    <link rel="stylesheet" href="assets/css/bookingOrder.css" />
  </head>
  <body>
  
  <!--header section starts-->

  <?php include'./include/header.php'; ?>

  <!--header section ends-->

  <div class="main">
      <h1 class="heading"><span>Booking</span> history</h1>
      <div class="upperPart">
        <div class="booking">
            <p><?php echo $countData['total_bookings'] ?? 0; ?> bookings</p>
        </div>
        <div class="booking">
            <p><?php echo $countData['confirmed_bookings'] ?? 0; ?> confirm bookings</p>
        </div>
        <div class="booking">
            <p><?php echo $countData['pending_bookings'] ?? 0; ?> pending bookings</p>
        </div>
        <div class="booking">
            <p><?php echo $countData['cancelled_bookings'] ?? 0; ?> cancel bookings</p>
        </div>
      </div>
      <div class="lowerPart">
        <?php 

        if(mysqli_num_rows($bookingResult)>0){
            while($row = mysqli_fetch_assoc($bookingResult)){
                $formattedDate = date("d-m-Y", strtotime($row['dateOfBooking']));
                $formattedPrice = number_format($row['totalPrice']);
                echo'<div class="bookingHistory">
                        <div class="packageType">
                            <h2>Service Type</h2>
                            <p>'.htmlspecialchars($row['packageName']).'</p>
                        </div>
                        <div class="dateOfBooking">
                            <h2>Date of Booking</h2>
                            <p>'. $formattedDate .'</p>
                        </div>
                        <div class="totalPrice">
                            <h2>Total Price</h2>
                            <p>'. $formattedPrice .'</p>
                        </div>
                        <div class="sitting">
                            <h2>Total Seats</h2>
                            <p>'.htmlspecialchars($row['totalSeats']).'</p>
                        </div>
                        <div class="bookingStatus">
                            <h2>Booking Status</h2>
                            <p>'.htmlspecialchars($row['status']).'</p>
                        </div>
                    </div>';
            }
        }else{
            echo '<div class="no-bookings">
                        <p>No bookings found.</p>
                    </div>';
        }
        ?>
      </div>
  </div>

  <!-- footer section starts -->

  <?php include'./include/footer.php'; ?>

  <!-- footer section ends -->

  <!-- theme toggler starts-->

  <?php include'./include/themeToggler.php'; ?>

  <!-- theme toggler ends -->

<?php include'./include/scriptSection.php'; ?>