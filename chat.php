<?php
session_start();
include 'admin/connect.php';

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['customerId'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to chat']);
    exit;
}

$hallId = (int)$_GET['hallId'] ?? 0;
$userId = (int)$_SESSION['customerId'];

if ($hallId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid hall ID']);
    exit;
}

try {
    // Send message
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Message cannot be empty']);
            exit;
        }
        
        $stmt = $con->prepare("INSERT INTO chatmessages 
                              (hallId, userId, message, sender) 
                              VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("iis", $hallId, $userId, $message);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
        exit;
    }

    // Get messages
    $stmt = $con->prepare("SELECT *, 
                          DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:%s') as formatted_time 
                          FROM chatmessages 
                          WHERE hallId = ? AND userId = ?
                          ORDER BY timestamp ASC");
    $stmt->bind_param("ii", $hallId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);

    // Mark hall messages as read
    $con->query("UPDATE chatmessages SET isRead = TRUE 
                WHERE hallId = $hallId AND userId = $userId 
                AND sender = 'hall' AND isRead = FALSE");

    echo json_encode($messages);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>