<?php
session_start();
require_once '../connection/connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : null;

if ($receiver_id) {
    $stmt = $conn->prepare("SELECT sender_id, recipient_id, message, timestamp 
                            FROM chat_messages 
                            WHERE (sender_id = ? AND recipient_id = ?) 
                               OR (sender_id = ? AND recipient_id = ?) 
                            ORDER BY timestamp ASC");
    $stmt->bind_param('iiii', $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['new_messages' => $messages]);
    $stmt->close();
}
?>
