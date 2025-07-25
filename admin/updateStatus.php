<?php
include 'connect.php';

if(isset($_GET['booking_id'])) {
    $booking_id = mysqli_real_escape_string($con, $_GET['booking_id']);
    $alert = '';
    
    // Start transaction for atomic updates
    mysqli_begin_transaction($con);
    
    try {
        // Handle Booking Status Update
        if(isset($_GET['status'])) {
            $status = mysqli_real_escape_string($con, $_GET['status']);
            $updateBooking = mysqli_query($con, "UPDATE booking SET status = '$status' WHERE bookingId = '$booking_id'");
            
            if (!$updateBooking) {
                throw new Exception("Failed to update booking status");
            }
            
            $alert = "<script>alert('Booking status updated successfully');</script>";
        }
        
        // Handle Payment Status Update
        if(isset($_GET['payment'])) {
            $payment_status = mysqli_real_escape_string($con, $_GET['payment']);
            $updatePayment = mysqli_query($con, "UPDATE payment SET paymentStatus = '$payment_status' WHERE bookingId = '$booking_id'");
            
            if (!$updatePayment) {
                throw new Exception("Failed to update payment status");
            }
            
            $alert = "<script>alert('Payment status updated successfully');</script>";
        }
        
        mysqli_commit($con);
    } catch(Exception $e) {
        mysqli_rollback($con);
        $alert = "<script>alert('Error updating status: " . addslashes($e->getMessage()) . "');</script>";
    }
    
    // Show alert and redirect
    echo $alert;
    echo "<script>window.location.href='booking.php';</script>";
    exit();
} else {
    echo "<script>alert('Invalid request'); window.location.href='booking.php';</script>";
    exit();
}
?>