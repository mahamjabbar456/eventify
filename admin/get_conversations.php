<?php
session_start();
include 'connect.php';

$userId = $_SESSION['userId'];
$role = $_SESSION['roleName']; // Get role from session

$query = "SELECT 
            cm.userId, 
            cm.hallId, 
            c.name as userName,
            h.name as hallName,
            MAX(cm.timestamp) as lastTime,
            (SELECT message FROM chatmessages 
             WHERE userId = cm.userId AND hallId = cm.hallId 
             ORDER BY timestamp DESC LIMIT 1) as lastMessage,
            SUM(CASE WHEN cm.sender = 'user' AND cm.isRead = FALSE THEN 1 ELSE 0 END) as unread
          FROM chatmessages cm
          JOIN customer c ON cm.userId = c.customerId
          JOIN hall h ON cm.hallId = h.id";


if ($role !== 'Admin') {
    $query .= " JOIN assignhall ah ON h.id = ah.hallId
                WHERE ah.userId = $userId";
}

$query .= " GROUP BY cm.userId, cm.hallId, c.name, h.name
            ORDER BY lastTime DESC";

$result = mysqli_query($con, $query);
$conversations = mysqli_fetch_all($result, MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($conversations);
?>