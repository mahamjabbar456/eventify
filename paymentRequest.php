<?php
// Easypaisa API Configuration
$storeId = "YOUR_STORE_ID"; // Provided by Easypaisa
$hashKey = "YOUR_HASH_KEY"; // Provided by Easypaisa
$merchantId = "YOUR_MERCHANT_ID"; // Provided by Easypaisa
$postUrl = "https://easypay.easypaisa.com.pk/easypay/Index.jsf"; // Easypaisa Payment URL

// Order Details
$orderId = "ORD" . time(); // Unique Order ID
$amount = 1000; // Amount in PKR
$mobileNumber = "03001234567"; // Customer's mobile number (optional)
$email = "customer@example.com"; // Customer's email (optional)
$description = "Payment for Order #" . $orderId;

// Generate Hash for Security
$hashRequest = $storeId . $amount . $orderId . $mobileNumber . $email . $hashKey;
$hash = hash('sha256', $hashRequest);

// Redirect to Easypaisa Payment Page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Easypaisa...</title>
</head>
<body>
    <form id="easypaisaForm" action="<?php echo $postUrl; ?>" method="POST">
        <input type="hidden" name="storeId" value="<?php echo $storeId; ?>">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="postBackURL" value="https://yourwebsite.com/payment_response.php">
        <input type="hidden" name="orderRefNum" value="<?php echo $orderId; ?>">
        <input type="hidden" name="mobileAccountNo" value="<?php echo $mobileNumber; ?>">
        <input type="hidden" name="emailAddress" value="<?php echo $email; ?>">
        <input type="hidden" name="paymentMethod" value="MA">
        <input type="hidden" name="merchantHashedReq" value="<?php echo $hash; ?>">
    </form>
    <script>document.getElementById("easypaisaForm").submit();</script>
</body>
</html>