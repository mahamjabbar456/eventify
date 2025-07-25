<!-- Include header and sidebar components -->
<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>
<?php
include 'connect.php';

// Enhanced function to check hall availability with AC charge info
function checkHallAvailability($con, $hallId, $date, $timeSlot, $currentBookingId = null, $requestedSeats = 0) {
    $query = "SELECT 
                h.capacity, 
                h.event_capacity,
                pkg.acChargesPrices,
                pkg.acRates,
                (SELECT COUNT(*) FROM booking 
                 WHERE hallId = $hallId 
                 AND dateOfBooking = '$date' 
                 AND timeSlot = '$timeSlot'
                 AND status != 'cancelled'
                 AND bookingId != '$currentBookingId') as booked_events,
                (SELECT SUM(totalSeats) FROM booking 
                 WHERE hallId = $hallId 
                 AND dateOfBooking = '$date' 
                 AND timeSlot = '$timeSlot'
                 AND status != 'cancelled'
                 AND bookingId != '$currentBookingId') as booked_seats
              FROM hall h
              LEFT JOIN package pkg ON pkg.packageId = (
              SELECT b.packageId 
              FROM booking b 
              WHERE b.bookingId = '$currentBookingId'
              LIMIT 1
              )
              WHERE h.id = $hallId";
    
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        
        // Calculate remaining capacity
        $remainingSeats = $data['capacity'] - ($data['booked_seats'] ?: 0);
        $canAccommodate = ($remainingSeats >= $requestedSeats);
        
        return [
            'capacity' => $data['capacity'],
            'event_capacity' => $data['event_capacity'],
            'booked_events' => $data['booked_events'],
            'booked_seats' => $data['booked_seats'] ?: 0,
            'remaining_seats' => $remainingSeats,
            'can_accommodate' => $canAccommodate,
            'ac_charge_type' => $data['acChargesPrices'],
            'ac_rate' => $data['acRates']
        ];
    }
    return false;
}

// Check if the 'id' parameter is set in the URL to fetch booking details
if (isset($_GET['id'])) {
    $booking_id = mysqli_real_escape_string($con, $_GET['id']);
    
    // Query to fetch booking details with hall and payment information
    $query = "SELECT 
                b.*,
                h.name AS hall_name,
                h.id AS hall_id,
                h.capacity,
                h.event_capacity,
                p.totalPayment,
                p.paidPayment,
                p.remainingPayment,
                p.paymentMethod,
                p.paymentStatus,
                pkg.acChargesPrices,
                pkg.acRates,
                b.discount
              FROM booking b
              JOIN hall h ON b.hallId = h.id
              LEFT JOIN payment p ON b.bookingId = p.bookingId
              LEFT JOIN package pkg ON b.packageId = pkg.packageId
              WHERE b.bookingId = '$booking_id'";
    
    $result = mysqli_query($con, $query);
    
    // If the booking is found, fetch its details
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $bookingId = $row['bookingId'];
        $hallId = $row['hallId'];
        $packageType = $row['packageType'];
        $hallName = $row['hall_name'];
        $hallCapacity = $row['capacity'];
        $eventCapacity = $row['event_capacity'];
        // $acChargeType = $row['ac_charge_type'];
        // $acRate = $row['ac_rate'];
        $fullName = $row['fullName'];
        $email = $row['email'];
        $phoneNo = $row['phoneNo'];
        $cnic = $row['cnic'];
        $dateOfBooking = $row['dateOfBooking'];
        $timeSlot = $row['timeSlot'];
        $totalSeats = $row['totalSeats'];
        $acInclude = $row['includeAC'];
        $acRates = $row['acRates'];
        $acChargesPrices = $row['acCharges'];
        $TotalPayment = $row['totalPayment'];
        $paidPayment = $row['paidPayment'];
        $remainingPayment = $row['remainingPayment'];
        $paymentMethod = $row['paymentMethod'];
        $paymentStatus = $row['paymentStatus'];
        $status = $row['status'];
        $menu = $row['menu'];
        $discount = $row['discount'];
    } else {
        echo "<script>alert('Booking not found!'); window.location='booking.php';</script>";
        exit();
    }
}

// Handle form submission for updating booking details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $_SESSION['form_data'] = $_POST;
    // Retrieve the updated values from the form input fields
    $booking_id = mysqli_real_escape_string($con, $_POST['booking_id']);
    $fullName = mysqli_real_escape_string($con, $_POST['fullName']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    // $phoneNo = mysqli_real_escape_string($con, $_POST['phoneNo']);
    // $cnic = mysqli_real_escape_string($con, $_POST['cnic']);
    $cnic = mysqli_real_escape_string($con, str_replace('-', '', $_POST['cnic']));
    $phoneNo = mysqli_real_escape_string($con, str_replace('-', '', $_POST['phoneNo']));
    $dateOfBooking = mysqli_real_escape_string($con, $_POST['dateOfBooking']);
    $newTimeSlot = mysqli_real_escape_string($con, $_POST['timeSlot']);
    $totalSeats = mysqli_real_escape_string($con, $_POST['totalSeats']);
    $orderPayment = mysqli_real_escape_string($con, $_POST['totalPayment']);
    $acChargesInclude = isset($_POST['acChargesInclude']) ? (int)$_POST['acChargesInclude'] : 0;
    $paymentMethod = mysqli_real_escape_string($con, $_POST['paymentMethod']);
    $menu = mysqli_real_escape_string($con, $_POST['menu']);
    $discount = mysqli_real_escape_string($con, $_POST['discount']) ? (int)$_POST['discount'] : 0;

    // Begin transaction
    mysqli_begin_transaction($con);
    
    try {
        $validationErrors = [];
    
        // Check if time slot or date or seats changed
        if ($newTimeSlot != $timeSlot || $dateOfBooking != $row['dateOfBooking'] || $totalSeats != $row['totalSeats']) {
            $availability = checkHallAvailability($con, $hallId, $dateOfBooking, $newTimeSlot, $booking_id, $totalSeats);
            
            if (!$availability) {
                $validationErrors[] = "Error checking hall availability";
            } else {
                // Check event capacity
                if ($availability['booked_events'] >= $availability['event_capacity']) {
                    $validationErrors[] = "This time slot already has the maximum {$availability['event_capacity']} events booked";
                }
                
                // Check seating capacity
                if (!$availability['can_accommodate']) {
                    $validationErrors[] = "Not enough capacity available. Only {$availability['remaining_seats']} seats remaining in this time slot";
                }
            }
        }
        
        // If there are validation errors, show them and stop processing
        if (!empty($validationErrors)) {
            throw new Exception(implode("\n", $validationErrors));
        }
        
        
        // Helper function to calculate AC charges
        function calculateACCharges($con, $seats, $includeAC, $acRateType, $acRateValue) {
            $charges = 0;
            if ($includeAC) {
                if ($acRateType === 'perPerson') {
                    $charges = $acRateValue * $seats;
                } else {
                    // Fixed rate - default to 3 hours
                    $charges = $acRateValue * 3;
                }
            }
            return $charges;
        }
// After getting POST data
// After getting POST data
$isCustomPackage = ($row['packageType'] === 'customPackage');

if ($isCustomPackage && $row['totalPayment'] == 0) {
    // For new custom packages (zero payment) - use manual input
    $manualPrice = floatval($_POST['totalPayment']);
    $acCharges = calculateACCharges($con, $totalSeats, $acChargesInclude, $row['acRates'], $row['acChargesPrices']);
    $orderPrice = $manualPrice + $acCharges;
} else {
    // For standard packages OR existing custom packages (payment > 0)
    $basePriceWithoutAC = $row['orderPrice'] - $row['acCharges'];
    $basePricePerSeat = $basePriceWithoutAC / max(1, $row['totalSeats']);
    $acCharges = calculateACCharges($con, $totalSeats, $acChargesInclude, $row['acRates'], $row['acChargesPrices']);
    $orderPrice = ($basePricePerSeat * $totalSeats) + $acCharges;
}

// Apply discount
$totalPayment = $orderPrice - ($orderPrice * $discount / 100);
$remainingPayment = max(0, $totalPayment - $paidPayment);
        
        // Update payment status based on amounts
        if ($remainingPayment <= 0) {
            $newPaymentStatus = 'completed';
        } elseif ($paidPayment > 0) {
            $newPaymentStatus = 'partial';
        } else {
            $newPaymentStatus = 'pending';
        }
        
        // Update booking table
        $bookingSql = "UPDATE booking SET 
                        fullName = '$fullName',
                        email = '$email',
                        phoneNo = '$phoneNo',
                        cnic = '$cnic',
                        dateOfBooking = '$dateOfBooking',
                        timeSlot = '$newTimeSlot',
                        totalSeats = '$totalSeats',
                        includeAC = '$acChargesInclude',
                        acCharges = '$acCharges',
                        status = '$status',
                        totalPrice = '$totalPayment',
                        orderPrice = '$orderPrice',
                        menu = '$menu',
                        discount = '$discount'
                      WHERE bookingId = '$booking_id'";
        
        if (!mysqli_query($con, $bookingSql)) {
            throw new Exception("Booking update failed: " . mysqli_error($con));
        }
        
        // Update payment table
        $paymentSql = "UPDATE payment SET 
                        totalPayment = '$totalPayment',
                        paidPayment = '$paidPayment',
                        remainingPayment = '$remainingPayment',
                        paymentMethod = '$paymentMethod',
                        paymentStatus = '$newPaymentStatus'
                      WHERE bookingId = '$booking_id'";
        
        if (!mysqli_query($con, $paymentSql)) {
            throw new Exception("Payment update failed: " . mysqli_error($con));
        }
        
        // Commit transaction if all validations pass
        mysqli_commit($con);
        echo "<script>alert('Booking updated successfully!'); window.location='booking.php';</script>";
        exit();
    } catch (Exception $e) {
        // Rollback transaction if any validation fails
        mysqli_rollback($con);
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>
<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Update Booking (BK-<?= $bookingId ?>)</h2>
        
        <!-- Form for updating booking details -->
        <form method="post" enctype="multipart/form-data" id="bookingForm">
            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
            <input type="hidden" name="orderPrice" value="<?= $row['orderPrice'] ?>">
            <input type="hidden" id="acRateType" value="<?= $row['acRates'] ?>">
            <input type="hidden" id="acRateValue" value="<?= $row['acChargesPrices'] ?>">
            <!-- Add this hidden field after loading booking data -->
            <input type="hidden" id="packageType" value="<?= $row['packageType'] ?>">
            
            <div class="form-row">
                <!-- Hall Information (readonly) -->
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold">Hall</label>
                    <input type="text" class="form-control" value="<?= $hallName ?>" readonly>
                </div>
                
                <!-- Booking Date -->
                <div class="col-md-6 mb-3">
                    <label for="dateOfBooking" class="font-weight-bold">Booking Date</label>
                    <input type="date" class="form-control" id="dateOfBooking" name="dateOfBooking" value="<?= $dateOfBooking ?>" required>
                </div>
                
                <!-- Time Slot -->
                <div class="col-md-6 mb-3">
                    <label for="timeSlot" class="font-weight-bold">Time Slot</label>
                    <select class="form-control" id="timeSlot" name="timeSlot" required>
                        <option value="morning" <?= $timeSlot == 'morning' ? 'selected' : '' ?>>Morning</option>
                        <option value="afternoon" <?= $timeSlot == 'afternoon' ? 'selected' : '' ?>>Afternoon</option>
                        <option value="evening" <?= $timeSlot == 'evening' ? 'selected' : '' ?>>Evening</option>
                        <option value="night" <?= $timeSlot == 'night' ? 'selected' : '' ?>>Night</option>
                    </select>
                </div>
                
                <!-- Total Seats -->
                <div class="col-md-6 mb-3">
                    <label for="totalSeats" class="font-weight-bold">Total Seats</label>
                    <input type="number" class="form-control" id="totalSeats" name="totalSeats" 
                        value="<?= $totalSeats ?>" min="1" max="<?= $hallCapacity ?>" required>
                    <small class="form-text text-muted">Maximum capacity: <?= $hallCapacity ?> seats</small>
                    <div id="seatWarning" class="text-danger small" style="display:none;"></div>
                </div>
            </div>
            
            <h4 class="mt-4">Customer Information</h4>
            <div class="form-row">
                <!-- Full Name -->
                <div class="col-md-4 mb-3">
                    <label for="fullName" class="font-weight-bold">Full Name</label>
                    <input type="text" class="form-control" id="fullName" name="fullName" value="<?= $fullName ?>" required>
                </div>
                
                <!-- Email -->
                <div class="col-md-4 mb-3">
                    <label for="email" class="font-weight-bold">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $email ?>" required>
                </div>
                
                <!-- Phone Number -->
                <div class="col-md-4 mb-3">
                    <label for="phoneNo" class="font-weight-bold">Phone Number</label>
                    <input type="tel" class="form-control" id="phoneNo" name="phoneNo" value="<?= $phoneNo ?>" required>
                </div>
                
                <!-- CNIC -->
                <div class="col-md-4 mb-3">
                    <label for="cnic" class="font-weight-bold">CNIC</label>
                    <input type="text" class="form-control" id="cnic" name="cnic" value="<?= $cnic ?>" required>
                </div>
            </div>

            <h4 class="mt-4">AC Information</h4>
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label for="acChargesInclude" class="font-weight-bold">AC Include</label>
                    <select class="form-control" id="acChargesInclude" name="acChargesInclude" required>
                        <option value="0" <?= $acInclude == 0 ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= $acInclude == 1 ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">AC Rate</label>
                    <input type="text" class="form-control" name="acRates" value="<?= $acRates ?>" readonly>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="acCharges" class="font-weight-bold">Total AC Charges (PKR)</label>
                    <input type="number" class="form-control" id="acCharges" name="acCharges" 
                        value="<?= $row['acCharges'] ?>" readonly>
                </div>
            </div>
            
            <h4 class="mt-4">Payment Information</h4>
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label for="discount" class="font-weight-bold">Discount (%)</label>
                    <input type="number" step="0.01" class="form-control" id="discount" name="discount" 
                        value="<?= $discount ?>" min="0" max="100">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="totalPayment" class="font-weight-bold">Total Amount (PKR)</label>
                    <input type="number" class="form-control" id="totalPayment" name="totalPayment" 
                    value="<?= $row['totalPayment'] ?>"
                    <?= ($row['packageType'] === 'customPackage' && $row['totalPayment'] === 0) ? '' : 'readonly' ?>>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Final Amount (PKR)</label>
                    <input type="text" class="form-control" id="finalAmount" 
                        value="<?= number_format($row['totalPayment'], 2) ?>" readonly>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Paid Amount (PKR)</label>
                    <input type="text" class="form-control" value="<?= number_format($paidPayment, 2) ?>" readonly>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Remaining Amount (PKR)</label>
                    <input type="text" class="form-control" id="remainingPayment" 
                        value="<?= number_format($remainingPayment, 2) ?>" readonly>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="paymentMethod" class="font-weight-bold">Payment Method</label>
                    <select class="form-control" id="paymentMethod" name="paymentMethod" required>
                        <option value="Cash" <?= $paymentMethod == 'Cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="JazzCash" <?= $paymentMethod == 'JazzCash' ? 'selected' : '' ?>>JazzCash</option>
                    </select>
                </div>
            </div>
            
            <h4 class="mt-4">Menu</h4>
            <div class="form-row">
                <div class="col-md-12 mb-3">
                    <label for="menu" class="font-weight-bold">Menu</label>
                    <textarea class="form-control" id="menu" name="menu" rows="3"><?= 
                    //  htmlspecialchars(
                    //     str_replace(
                    //         ['\r\n', '\r', '\n', "\r\n", "\r", "\n"], 
                    //         "\n", 
                    //         $menu
                    //     ),
                    //     ENT_QUOTES, 
                    //     'UTF-8', 
                    //     false
                    htmlspecialchars(
                        str_replace(
                            ['\r\n', "\r\n"], 
                            "\n", 
                            $menu
                        ),
                        ENT_QUOTES, 
                        'UTF-8', 
                        false
                    ) ?></textarea>
                </div>
            </div>
            
            <!-- Submit and Cancel buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="update">Update Booking</button>
                <a href="booking.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get package type
    const packageType = document.getElementById('packageType').value;
    const isCustomPackage = packageType === 'customPackage';
    
    // Original pricing data
    const originalOrderPrice = parseFloat("<?= $row['orderPrice'] ?>") || 0;
    const originalACCharges = parseFloat("<?= $row['acCharges'] ?>") || 0;
    const originalSeats = parseInt("<?= $row['totalSeats'] ?>") || 1;
    const originalTotalPayment = parseFloat("<?= $TotalPayment ?>") || 0;
    
    // AC configuration
    const acRateType = "<?= $row['acRates'] ?>";
    const acRateValue = parseFloat("<?= $row['acChargesPrices'] ?>") || 0;
    
    // DOM elements
    const totalPaymentInput = document.getElementById('totalPayment');
    const seatsInput = document.getElementById('totalSeats');
    const acIncludeSelect = document.getElementById('acChargesInclude');
    const discountInput = document.getElementById('discount');
    
    // Set initial readonly state
    totalPaymentInput.readOnly = !(isCustomPackage && originalTotalPayment == 0);
    
    // Calculate AC charges
    function calculateACCharges(seats) {
        const includeAC = acIncludeSelect.value === '1';
        let charges = 0;
        
        if (includeAC) {
            if (acRateType === 'perPerson') {
                charges = acRateValue * seats;
            } else { // fixed rate
                charges = acRateValue * 3; // 3 hours default
            }
        }
        document.getElementById('acCharges').value = charges.toFixed(2);
        return charges;
    }

    // Update all payment calculations
    // function updateTotalPayment() {
    //     const seats = parseInt(seatsInput.value) || 0;
    //     const acCharges = calculateACCharges(seats);
    //     const discount = parseFloat(discountInput.value) || 0;
    //     const paidPayment = parseFloat('<?= $paidPayment ?>') || 0;
        
    //     let orderPrice, totalPayment;
        
    //     if (isCustomPackage) {
    //         // For custom packages - use manual input plus AC charges
    //         const manualPrice = parseFloat(totalPaymentInput.value) || 0;
    //         orderPrice = manualPrice + acCharges;
            
    //         // If seats changed, adjust price proportionally
    //         if (seats !== originalSeats && originalSeats > 0) {
    //             const seatRatio = seats / originalSeats;
    //             const adjustedPrice = (originalTotalPayment - originalACCharges) * seatRatio;
    //             totalPaymentInput.value = (adjustedPrice + acCharges).toFixed(2);
    //             orderPrice = adjustedPrice + acCharges;
    //         }
    //     } else {
    //         // For standard packages - calculate based on seats
    //         const basePriceWithoutAC = originalOrderPrice - originalACCharges;
    //         const basePricePerSeat = basePriceWithoutAC / originalSeats;
    //         orderPrice = (basePricePerSeat * seats) + acCharges;
    //         totalPaymentInput.value = orderPrice.toFixed(2);
    //     }
        
    //     // Apply discount
    //     totalPayment = orderPrice - (orderPrice * discount / 100);
        
    //     // Update fields
    //     document.querySelector('input[name="orderPrice"]').value = orderPrice.toFixed(2);
    //     document.getElementById('remainingPayment').value = Math.max(0, totalPayment - paidPayment).toFixed(2);
    // }
   function updateTotalPayment() {
    const seats = parseInt(seatsInput.value) || 0;
    const acCharges = calculateACCharges(seats);
    const discount = parseFloat(discountInput.value) || 0;
    const paidPayment = parseFloat('<?= $paidPayment ?>') || 0;
    
    let orderPrice, totalPayment;
    
    if (isCustomPackage && originalTotalPayment == 0) {
        // For new custom packages - use manual input
        const manualPrice = parseFloat(totalPaymentInput.value) || 0;
        orderPrice = manualPrice + acCharges;
    } else {
        // For standard packages OR existing custom packages
        const basePriceWithoutAC = originalOrderPrice - originalACCharges;
        const basePricePerSeat = basePriceWithoutAC / originalSeats;
        orderPrice = (basePricePerSeat * seats) + acCharges;
        totalPaymentInput.value = orderPrice.toFixed(2);
    }
    
    // Apply discount
    totalPayment = orderPrice - (orderPrice * discount / 100);
    
    // Update all fields
    document.querySelector('input[name="orderPrice"]').value = orderPrice.toFixed(2);
    document.getElementById('finalAmount').value = totalPayment.toFixed(2); // This is the new line
    document.getElementById('remainingPayment').value = Math.max(0, totalPayment - paidPayment).toFixed(2);
}

    // Event listeners
    acIncludeSelect.addEventListener('change', updateTotalPayment);
    seatsInput.addEventListener('change', updateTotalPayment);
    discountInput.addEventListener('input', updateTotalPayment);
    
    if (isCustomPackage) {
        totalPaymentInput.addEventListener('input', updateTotalPayment);
    }
    
    // Initialize
    updateTotalPayment();
});

$(document).ready(function() {
    // Phone number masking
    $('input[name="phoneNo"]').inputmask({
        mask: '0399-9999999',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
    $('input[name="cnic"]').inputmask({
        mask: '99999-9999999-9',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
});
</script>

<!-- Include footer component -->
<?php include './includes/footer.php'; ?>