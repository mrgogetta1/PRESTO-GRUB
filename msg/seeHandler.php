<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connection/connection.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Get user ID from session or request
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "data: User not logged in\n\n";
    flush();
    exit;
}

// Poll for new messages in a loop
while (true) {
    // Query to get the latest unread messages for this user
    $query = "SELECT cm.message, cm.sender_id, cm.timestamp, o.order_id 
              FROM chat_messages cm
              JOIN orders o ON cm.order_id = o.order_id
              WHERE cm.recipient_id = ? AND cm.is_read = 0
              ORDER BY cm.timestamp ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $newMessages = [];
    while ($row = $result->fetch_assoc()) {
        $newMessages[] = $row;
    }

    if (!empty($newMessages)) {
        echo "data: " . json_encode($newMessages) . "\n\n";
        flush(); // Send the data to the client immediately
    }

    // Sleep to prevent overloading the server (adjust as needed)
    sleep(5);
}
?>