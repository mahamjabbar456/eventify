<?php
session_start();
include 'connect.php';

$userId = $_POST['userId'];
$hallId = $_POST['hallId'];
$message = mysqli_real_escape_string($con, $_POST['message']);
$sender = $_POST['sender']; // 'hall' or 'user'

// Verify hall owner has access if sender is hall
if ($sender === 'hall') {
    $check = mysqli_query($con, "SELECT 1 FROM assignhall 
                               WHERE hallId = $hallId AND userId = {$_SESSION['userId']}");
    if (mysqli_num_rows($check) == 0) {
        http_response_code(403);
        exit;
    }
}

// Insert message
$query = "INSERT INTO chatmessages 
          (userId, hallId, message, sender, isRead) 
          VALUES ($userId, $hallId, '$message', '$sender', FALSE)";
mysqli_query($con, $query);

echo json_encode(['success' => true]);
?>