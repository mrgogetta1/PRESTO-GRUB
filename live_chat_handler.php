<?php
session_start();

// Display errors for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

require_once 'connection/connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in or role not set.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $recipient_id = $_POST['recipient_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';

    if (empty($message) || empty($recipient_id) || empty($order_id)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit;
    }

    // Prepare query for inserting into the chat_messages table
    $query = "INSERT INTO chat_messages (sender_id, recipient_id, message, order_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement.']);
        exit;
    }

    $stmt->bind_param("iisi", $user_id, $recipient_id, $message, $order_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message.']);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $order_id = $_GET['order_id'] ?? '';

    if (empty($order_id)) {
        echo json_encode(['success' => false, 'error' => 'Order ID is required.']);
        exit;
    }

    // Prepare query for selecting messages ordered by timestamp
    $query = "SELECT * FROM chat_messages WHERE order_id = ? ORDER BY timestamp ASC";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement.']);
        exit;
    }

    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['success' => true, 'messages' => $messages]);

    $stmt->close();
}
?>