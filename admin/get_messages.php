<?php
session_start();
include 'connect.php';

$userId = $_GET['userId'];
$hallId = $_GET['hallId'];
$role = $_SESSION['roleName'];

// Verify access rights
if ($role !== 'Admin') {
    $check = mysqli_query($con, "SELECT 1 FROM assignhall 
                               WHERE hallId = $hallId AND userId = {$_SESSION['userId']}");
    if (mysqli_num_rows($check) == 0) {
        http_response_code(403);
        exit;
    }
}

// Mark messages as read FIRST
mysqli_query($con, "UPDATE chatmessages SET isRead = TRUE 
                   WHERE userId = $userId AND hallId = $hallId 
                   AND sender = 'user' AND isRead = FALSE");

// Then get the messages
$query = "SELECT * FROM chatmessages 
          WHERE userId = $userId AND hallId = $hallId
          ORDER BY timestamp ASC";
          
$result = mysqli_query($con, $query);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
?>