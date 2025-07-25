<?php
// Easypaisa API Configuration
$storeId = "YOUR_STORE_ID";
$hashKey = "YOUR_HASH_KEY";

// Get Response from Easypaisa
$orderId = $_POST['orderRefNum'];
$paymentStatus = $_POST['paymentStatus']; // "Paid" or "Unpaid"
$amount = $_POST['amount'];
$transactionId = $_POST['transactionId'];
$receivedHash = $_POST['merchantHashedReq'];

// Verify Hash for Security
$expectedHash = hash('sha256', $storeId . $orderId . $amount . $paymentStatus . $hashKey);

if ($receivedHash == $expectedHash) {
    if ($paymentStatus == "Paid") {
        // Payment Successful
        echo "Payment Successful! Transaction ID: " . $transactionId;
        // Update your database here (mark order as paid)
    } else {
        // Payment Failed
        echo "Payment Failed!";
    }
} else {
    // Invalid Hash (Possible Fraud)
    echo "Security Error: Invalid Payment Response";
}
?>