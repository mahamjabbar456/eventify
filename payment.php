<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once  'phpmail/src/Exception.php';
require_once  'phpmail/src/PHPMailer.php';
require_once  'phpmail/src/SMTP.php';

?>
<?php
session_start();
include 'admin/connect.php';

// Include your header and other template parts
include './include/startingSection.php';
?>
<link rel="stylesheet" href="assets/css/payment.css" />
  </head>
  <body>

<?php
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

if (!isset($_SESSION['booking_data'])) {
    header('Location: halls.php');
    exit();
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    // Retrieve booking data from session
    $bookingData = $_SESSION['booking_data'];
    
    // Start transaction
    mysqli_begin_transaction($con);
    
    $paymentMethod = mysqli_real_escape_string($con, $_POST['paymentMethod']);
    $customerEmail = mysqli_real_escape_string($con, $bookingData['email']);
    $customerName = mysqli_real_escape_string($con, $bookingData['fullName']);

    if ($bookingData['serviceType'] == 'customPackage') {
        // Custom package handling
        $bookingStatus = 'pending';
        $paymentStatus = 'pending';
        $bookingData['orderPrice'] = 0;
        $bookingData['discount'] = 0;
        $bookingData['totalPrice'] = 0;
        $paymentMethod = 'custom';
    } else {
        // Regular package handling
        if ($paymentMethod == 'jazzcash') {
            $bookingStatus = 'confirmed';
            $paymentStatus = 'partial'; // For 25% deposit
        } else { // COD
            $bookingStatus = 'pending';
            $paymentStatus = 'pending';
        }
    }

    // Insert into booking table
    $bookingQuery = "INSERT INTO booking (
        customerId, 
        packageId, 
        hallId, 
        packageType,
        fullName, 
        email, 
        phoneNo, 
        cnic, 
        dateOfBooking, 
        timeSlot, 
        totalSeats, 
        sittingType, 
        menu, 
        address, 
        includeAC,
        acCharges,
        orderPrice, 
        discount, 
        totalPrice, 
        status
    ) VALUES (
        '".mysqli_real_escape_string($con, $bookingData['customerId'])."',
        '".mysqli_real_escape_string($con, $bookingData['packageId'])."',
        '".mysqli_real_escape_string($con, $bookingData['hallId'])."',
        '".mysqli_real_escape_string($con, $bookingData['serviceType'])."',
        '".mysqli_real_escape_string($con, $bookingData['fullName'])."',
        '".mysqli_real_escape_string($con, $bookingData['email'])."',
        '".mysqli_real_escape_string($con, $bookingData['phoneNo'])."',
        '".mysqli_real_escape_string($con, $bookingData['cnic'])."',
        '".mysqli_real_escape_string($con, $bookingData['dateOfBooking'])."',
        '".mysqli_real_escape_string($con, $bookingData['timeSlot'])."',
        '".mysqli_real_escape_string($con, $bookingData['totalSeats'])."',
        '".mysqli_real_escape_string($con, $bookingData['sittingType'])."',
        '".mysqli_real_escape_string($con, $bookingData['menu'])."',
        '".mysqli_real_escape_string($con, $bookingData['address'])."',
        '".mysqli_real_escape_string($con, $bookingData['includeAC'])."',
        '".mysqli_real_escape_string($con, $bookingData['acCharges'])."',
        '".mysqli_real_escape_string($con, $bookingData['orderPrice'])."',
        '".mysqli_real_escape_string($con, $bookingData['discount'])."',
        '".mysqli_real_escape_string($con, $bookingData['totalPrice'])."',
        '".mysqli_real_escape_string($con, $bookingStatus)."'
    )";
    
    $bookingResult = mysqli_query($con, $bookingQuery);
    $bookingId = mysqli_insert_id($con);
    
    if (!$bookingResult || !$bookingId) {
        mysqli_rollback($con);
        showSweetAlert('error', 'Error', 'Failed to create booking. Please try again.','booking.php');
        // header('Location: booking_confirmation.php');
        exit();
    }

    if ($bookingData['serviceType'] == 'customPackage') {
        $paymentQuery = "INSERT INTO payment (
            bookingId,
            totalPayment,
            paidPayment,
            remainingPayment,
            paymentMethod,
            paymentStatus
        ) VALUES (
            '$bookingId',
            0, 
            0, 
            0, 
            'custom',
            'pending'
        )";
    } else {
        // Calculate payment values
        $totalPayment = $bookingData['totalPrice'];
        
        // Determine paid amount based on payment method
        if ($paymentMethod == 'cash') {
            $paidPayment = 0; // 0 for manual payment (will pay later)
        } else {
            $paidPayment = $totalPayment * 0.25; // 25% deposit for JazzCash
        }
        
        $remainingPayment = $totalPayment - $paidPayment;
        
        // Insert into payment table
        $paymentQuery = "INSERT INTO payment (
            bookingId,
            totalPayment,
            paidPayment,
            remainingPayment,
            paymentMethod,
            paymentStatus
        ) VALUES (
            '$bookingId',
            '$totalPayment',
            '$paidPayment',
            '$remainingPayment',
            '$paymentMethod',
            '$paymentStatus'
        )";
    }
    
    $paymentResult = mysqli_query($con, $paymentQuery);
    
    if (!$paymentResult) {
        mysqli_rollback($con);
        showSweetAlert('error', 'Error', 'Failed to process payment. Please try again.');
        header('Location: booking_confirmation.php');
        exit();
    }
    
    // Commit transaction
    mysqli_commit($con);
    
     try {
        $mail = new PHPMailer(true);
        
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "";
        $mail->Password = "";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom("eventifywebsite012@gmail.com", "Eventify Booking System");
        $mail->isHTML(true);
        
        if ($bookingData['serviceType'] == 'customPackage') {
            // Email to admin about custom package request
            $formattedMenu = str_replace(['\r\n'],'<br>', htmlspecialchars($bookingData['menu']));
            // $formattedMenu = nl2br(htmlspecialchars($bookingData['menu']));
            $mail->addAddress("admin@youreventplanner.com", "Admin");
            $mail->Subject = "New Custom Package Request #$bookingId";
            $mail->Body = "
                <h2>New Custom Package Request</h2>
                <p><strong>Booking ID:</strong> $bookingId</p>
                <p><strong>Customer:</strong> $customerName</p>
                <p><strong>Email:</strong> $customerEmail</p>
                <p><strong>Phone:</strong> {$bookingData['phoneNo']}</p>
                <p><strong>Event Date:</strong> {$bookingData['dateOfBooking']}</p>
                <p><strong>Time Slot:</strong> {$bookingData['timeSlot']}</p>
                <p><strong>Guest Count:</strong> {$bookingData['totalSeats']}</p>
                <p>Please contact the customer to discuss package details and pricing.</p>
            ";
            $mail->send();
            
            // Email to customer
            $mail->clearAddresses();
            $mail->addAddress($customerEmail, $customerName);
            $mail->Subject = "Your Custom Package Request #$bookingId";
            $mail->Body = "
                <h2>Thank You for Your Custom Package Request</h2>
                <p>Dear $customerName,</p>
                <p>We've received your request for a custom event package (Booking ID: $bookingId).</p>
                <p><strong>Event Details:</strong></p>
                <ul>
                    <li>Menu: {$formattedMenu}</li>
                    <li>Date: {$bookingData['dateOfBooking']}</li>
                    <li>Time: {$bookingData['timeSlot']}</li>
                    <li>Guest Count: {$bookingData['totalSeats']}</li>
                </ul>
                <p>Our team will contact you within 24 hours to discuss your requirements and provide pricing details.</p>
                <p>Thank you for choosing our service!</p>
            ";
            $mail->send();
            
            // Show SweetAlert 
            showSweetAlert('success', 'Request Submitted', 'Your custom package request has been received. Our team will contact you shortly.', 'index.php');
            exit();
            
        } elseif ($paymentMethod == 'cash') {
            // Manual payment confirmation email
            $mail->addAddress($customerEmail, $customerName);
            $mail->Subject = "Your Booking is Pending Payment #$bookingId";
            $mail->Body = "
                <h2>Booking Confirmation - Payment Pending</h2>
                <p>Dear $customerName,</p>
                <p>Your booking has been received (Booking ID: $bookingId).</p>
                <p><strong>Event Details:</strong></p>
                <ul>
                    <li>Date: {$bookingData['dateOfBooking']}</li>
                    <li>Time: {$bookingData['timeSlot']}</li>
                    <li>Guest Count: {$bookingData['totalSeats']}</li>
                    <li>Total Amount: {$bookingData['totalPrice']} PKR</li>
                </ul>
                <p>You selected manual payment method. Please complete your payment at our office to confirm your booking.</p>
                <p>Payment must be completed within 3 days to secure your booking.</p>
                <p>Thank you for choosing our service!</p>
            ";
            $mail->send();
            
            // Show SweetAlert
            showSweetAlert('success', 'Booking Received', 'Your booking is pending payment. Please complete payment within 3 days.', 'index.php');
            exit();
            
        } else {
            // JazzCash payment confirmation
            $mail->addAddress($customerEmail, $customerName);
            $mail->Subject = "Your Booking is Confirmed #$bookingId";
            $mail->Body = "
                <h2>Booking Confirmation</h2>
                <p>Dear $customerName,</p>
                <p>Your booking has been confirmed (Booking ID: $bookingId).</p>
                <p><strong>Event Details:</strong></p>
                <ul>
                    <li>Date: {$bookingData['dateOfBooking']}</li>
                    <li>Time: {$bookingData['timeSlot']}</li>
                    <li>Guest Count: {$bookingData['totalSeats']}</li>
                    <li>Total Amount: {$bookingData['totalPrice']} PKR</li>
                    <li>Deposit Paid: " . ($bookingData['totalPrice'] * 0.25) . " PKR</li>
                    <li>Remaining Balance: " . ($bookingData['totalPrice'] * 0.75) . " PKR</li>
                </ul>
                <p>Thank you for choosing our service!</p>
            ";
            $mail->send();
            
            // Show SweetAlert 
            showSweetAlert('success', 'Booking Confirmed', 'Your booking has been confirmed with 25% deposit paid.', 'index.php');
            exit();
        }
        
    } catch (Exception $e) {
        // Email failed but booking was successful
        error_log("Email sending failed: " . $e->getMessage());
        echo '<!DOCTYPE html><html><head><title>Redirecting</title>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '</head><body>';
        showSweetAlert('warning', 'Booking Confirmed', 'Your booking was successful but we couldn\'t send the confirmation email.', 'index.php');
        echo '</body></html>';
        exit();
    }
    
    // Clear session data
    unset($_SESSION['booking_data']);
    
    exit();
}
?>

<!-- Your existing header include -->
<?php include './include/header.php'; ?>
<section class="booking-confirmation">
    <div class="container">
        <h1 class="heading">Confirm Booking <span>& Payment</span></h1>
        
        <!-- Show booking summary -->
        <div class="booking-summary">
            <h3>Booking Summary</h3>
            <div class="summary-details">
                <div class="detail-row">
                    <span class="detail-label">Service Type:</span>
                    <span class="detail-value">
                        <?php 
                        switch($_SESSION['booking_data']['serviceType']) {
                            case 'sittingOnly': echo 'Sitting Only'; break;
                            case 'packageMenu': echo 'Standard Package'; break;
                            case 'customPackage': echo 'Custom Package'; break;
                            default: echo $_SESSION['booking_data']['serviceType'];
                        }
                        ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?= htmlspecialchars($_SESSION['booking_data']['fullName']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?= htmlspecialchars($_SESSION['booking_data']['dateOfBooking']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time Slot:</span>
                    <span class="detail-value"><?= ucfirst($_SESSION['booking_data']['timeSlot']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Seats:</span>
                    <span class="detail-value"><?= $_SESSION['booking_data']['totalSeats'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Price:</span>
                    <span class="detail-value"><?= $_SESSION['booking_data']['totalPrice'] ?> PKR</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">25% Deposit Price:</span>
                    <span class="detail-value"><?= $_SESSION['booking_data']['totalPrice'] * 0.25 ?> PKR</span>
                </div>
            </div>
        </div>
        
        <!-- Payment options -->
        <div class="payment-options">
            <h3>Payment Method</h3>
            <?php if ($_SESSION['booking_data']['serviceType'] == 'customPackage'): ?>
                <div class="custom-package-notice">
                    <p>For custom packages, our team will contact you with pricing details.</p>
                    <form action="" method="post" id="paymentForm">
                        <input type="hidden" name="paymentMethod" value="custom">
                        <button type="submit" name="confirm_booking" class="btn btn-primary">
                            Submit Custom Request
                        </button>
                    </form>
                </div>
            <?php else: ?>
            <form action="" method="post" id="paymentForm">
                <div class="form-group">
                    <input type="radio" name="paymentMethod" id="jazzcash" value="jazzcash" checked>
                    <label for="jazzcash">
                        <img src="assets/images/easypisalogo.png" alt="">
                    </label>
                </div>
                
                <div class="form-group">
                    <input type="radio" name="paymentMethod" id="cash" value="cash">
                    <label for="cash">Manual Payment</label>
                </div>
                
                <button type="submit" name="confirm_booking" class="btn btn-primary">Confirm & Pay Now</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include './include/footer.php'; ?>
<?php include './include/themeToggler.php'; ?>
<?php include './include/scriptSection.php'; ?>
