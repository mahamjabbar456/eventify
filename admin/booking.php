<?php
include 'connect.php';

if (isset($_GET['success'])) {
    echo "<script>alert('Status updated successfully');</script>";
}
if (isset($_GET['error'])) {
    echo "<script>alert('Error in Status updated');</script>";
}

?>

<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main" style="overflow-x:hidden;">
            <!-- Top navigation bar -->
            <?php include './includes/topNavBar.php'; ?>
            <div class="mainpart mt-4 mx-3">
                <h2><?php echo ($_SESSION['roleName'] == 'Hall Owner') ? 'My Hall Bookings' : 'All Bookings'; ?></h2>
                
                <div class="table-responsive" style="width: 100%; overflow-x: auto;">
                <table id="myTable" class="table table-striped table-bordered">
                    <thead class="th-background">
                        <tr class="th-background">
                            <th>Sr No</th>
                            <th>Hall Name</th>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>CNIC</th>
                            <th>BookingDate</th>
                            <th>TimeSlot</th>
                            <th>Seats</th>
                            <th>AC Include</th>
                            <th>AC Charges</th>
                            <th>Total Amount</th>
                            <th>Discount</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th>Payment Method</th>
                            <th>Booking Status</th>
                            <th>Payment Status</th>
                            <?php if ($_SESSION['roleName'] == 'Hall Owner') : ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include 'connect.php';
        
                          if ($_SESSION['roleName'] == 'Hall Owner') {
                        // For Hall Owners
                            $query = "SELECT 
                                        b.*,
                                        h.name AS hall_name,
                                        p.totalPayment,
                                        p.paidPayment,
                                        p.remainingPayment,
                                        p.paymentMethod,
                                        p.paymentStatus
                                    FROM booking b
                                    JOIN hall h ON b.hallId = h.id
                                    JOIN assignhall ah ON h.id = ah.hallId
                                    LEFT JOIN payment p ON b.bookingId = p.bookingId
                                    WHERE ah.userId = ".$_SESSION['userId']."
                                    ORDER BY b.dateOfBooking DESC, b.bookingId DESC";
                        } else {
                            // For Admins
                            $query = "SELECT 
                                        b.*,
                                        h.name AS hall_name,
                                        p.totalPayment,
                                        p.paidPayment,
                                        p.remainingPayment,
                                        p.paymentMethod,
                                        p.paymentStatus
                                    FROM booking b
                                    JOIN hall h ON b.hallId = h.id
                                    LEFT JOIN payment p ON b.bookingId = p.bookingId
                                    ORDER BY b.dateOfBooking DESC, b.bookingId DESC";
                        }
        
                        $result = mysqli_query($con, $query);
                        $sr = 1;
        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                // Format amounts with 2 decimal places
                                $totalPayment = number_format($row['totalPayment'], 2);
                                $paidPayment = number_format($row['paidPayment'], 2);
                                $remainingPayment = number_format($row['remainingPayment'], 2);
                
                                // Determine payment status color
                                $paymentStatusClass = '';
                                if($row['paymentStatus'] == 'completed') {
                                    $paymentStatusClass = 'text-success';
                                } elseif($row['paymentStatus'] == 'pending') {
                                    $paymentStatusClass = 'text-warning';
                                } else {
                                    $paymentStatusClass = 'text-danger';
                                }

                                $acInclude = $row['includeAC'] == 0 ? "No" : "Yes";
                
                                echo "<tr>
                                    <td>{$sr}</td>
                                    <td>{$row['hall_name']}</td>
                                    <td>BK-{$row['bookingId']}</td>
                                    <td>
                                        {$row['fullName']}
                                    </td>
                                    <td>
                                        {$row['email']}
                                    </td>
                                    <td>
                                        {$row['phoneNo']}
                                    </td>
                                    <td>
                                        {$row['cnic']}
                                    </td>
                                    <td>{$row['dateOfBooking']}</td>
                                    <td>{$row['timeSlot']}</td>
                                    <td>{$row['totalSeats']}</td>
                                    <td>{$acInclude}</td>
                                    <td>{$row['acCharges']}</td>
                                    <td>{$totalPayment} PKR</td>
                                    <td>{$row['discount']}%</td>
                                    <td>{$paidPayment} PKR</td>
                                    <td>{$remainingPayment} PKR</td>
                                    <td>{$row['paymentMethod']}</td>
                                    <td>{$row['status']}</td>
                                    <td class='{$paymentStatusClass}'>
                                        ".ucfirst($row['paymentStatus'])."
                                    </td>";
                                    if ($_SESSION['roleName'] == 'Hall Owner') {
                                    echo "<td>
                                        <div class='btn-group'>
                                            <!-- Booking Status Dropdown -->
                                            <div class='dropdown'>
                                                <button class='btn btn-sm btn-secondary dropdown-toggle mr-2' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                                    Booking
                                                </button>
                                                <ul class='dropdown-menu'>
                                                    <li><a class='dropdown-item' href='updateStatus.php?booking_id={$row['bookingId']}&status=done'>Done</a></li>
                                                    <li><a class='dropdown-item' href='updateStatus.php?booking_id={$row['bookingId']}&status=confirmed'>Confirmed</a></li>
                                                    <li><a class='dropdown-item' href='updateStatus.php?booking_id={$row['bookingId']}&status=cancel'>Cancel</a></li>
                                                    <li><a class='dropdown-item' href='updateStatus.php?booking_id={$row['bookingId']}&status=pending'>Pending</a></li>
                                                </ul>
                                            </div>

                                            <!-- Payment Status Dropdown -->
                                            <div class='dropdown ms-1'>
                                                <button class='btn btn-sm btn-info dropdown-toggle mr-2' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                                    Payment
                                                </button>
                                                <ul class='dropdown-menu'>
                                                    <li><a class='dropdown-item' href='updateStatus.php?booking_id={$row['bookingId']}&payment=pending'>Pending</a></li>
                                                    <li><a class='dropdown-item' href='updateStatus.php?booking_id={$row['bookingId']}&payment=partial'>Partial</a></li>
                                                    <li><a class='dropdown-item' href='updateStatus.php?booking_id={$row['bookingId']}&payment=completed'>Completed</a></li>
                                                    <li><a class='dropdown-item' href='addAmount.php?booking_id={$row['bookingId']}'>Amount</a></li>
                                                </ul>
                                            </div>

                                            <!-- Edit Button -->
                                            <a href='updateBooking.php?id={$row['bookingId']}' 
                                            class='btn btn-sm btn-warning mr-3' title='Edit'>
                                            <ion-icon name='create-outline'></ion-icon>
                                            </a>
                                        </div>
                                    </td>";
                                }
                                
                                echo "</tr>";
                                $sr++;
                            }
                        } else {
                            echo "<tr><td colspan='15' class='text-center'>No bookings found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>