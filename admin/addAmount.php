<!-- Include header and sidebar components -->
<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
    include 'connect.php';

    // Check if the 'id' parameter is set in the URL to fetch booking details
    if (isset($_GET['booking_id'])) {
        $booking_id = mysqli_real_escape_string($con, $_GET['booking_id']);

        $query = "SELECT * FROM payment WHERE bookingId = '$booking_id'";
        
        $result = mysqli_query($con, $query);
        
        // If the booking is found, fetch its details
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $bookingId = $row['bookingId'];
            $totalPayment = $row['totalPayment'];
            $paidPayment = $row['paidPayment'];
            $remainingPayment = $row['remainingPayment'];
        } else {
            echo "<script>alert('Booking not found!'); window.location='booking.php';</script>";
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
        $_SESSION['form_data'] = $_POST;
        $bookingId = mysqli_real_escape_string($con, $_POST['booking_id']);
        $addAmount = mysqli_real_escape_string($con, $_POST['paidPayment']);
        $comment = mysqli_real_escape_string($con, $_POST['comment']);

        // Check if remaining payment is already 0
        if ($addAmount <= 0) {
            echo "<script>alert('Payment amount must be greater than zero.');</script>";
        } else if($remainingPayment <= 0){
            echo "<script>alert('No remaining payment due. This booking is already fully paid.');</script>";
        }
        elseif ($addAmount > $remainingPayment) {
            echo "<script>alert('Payment cannot exceed the remaining amount (PKR " . number_format($remainingPayment, 2) . ").');</script>";
        } 
        // Process payment update if valid
        else {
            $newRemainingPayment = $remainingPayment - $addAmount;
            $newPaidPayment = $addAmount + $paidPayment; // Add to existing paid amount

            $transactionSql = "INSERT INTO transaction (transactionAmount,transactionDetail,bookingId) VALUES ('$addAmount', '$comment', '$bookingId')";
                            
            if(mysqli_query($con,$transactionSql)){
                $paymentSql = "UPDATE payment SET 
                                paidPayment = '$newPaidPayment',
                                remainingPayment = '$newRemainingPayment'
                                WHERE bookingId = '$bookingId'";

                if (mysqli_query($con, $paymentSql)) {
                echo "<script>
                        alert('Payment added successfully!');
                        window.location.href = 'booking.php';
                        </script>";
                } else {
                echo "<script>alert('Error updating payment: " . addslashes(mysqli_error($con)) . "');</script>";
                }
            } else {
                echo "<script>alert('Error updating transaction: " . addslashes(mysqli_error($con)) . "');</script>";
            }
            
        }
    }
?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Add Transaction</h2>
        
        <!-- Form for updating booking details -->
        <form method="post" enctype="multipart/form-data"  autocomplete="off">
            <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
            
                <div class="col-md-6 mb-3">
                    <label for="paidPayment" class="font-weight-bold">Paid Amount (PKR)</label>
                    <input type="number" class="form-control" name="paidPayment" value="<?php echo isset($_POST['paidPayment']) ? $_POST['paidPayment'] : ''; ?>" placeholder="Enter paid amount ">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="comment" class="font-weight-bold">Comment</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Write Comment"><?php echo isset($_POST['comment']) ? $_POST['comment'] : ''; ?></textarea>
                </div>
            <!-- Submit and Cancel buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="add">Add Amount</button>
                <a href="booking.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<!-- Include footer component -->
<?php include './includes/footer.php'; ?>