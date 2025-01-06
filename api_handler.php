<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');
require_once 'connection/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'fetchMessages':
        // Fetch messages (support ratchet logic for new messages only)
        $sender_id = $_GET['sender_id'] ?? null;
        $recipient_id = $_GET['recipient_id'] ?? null;
        $last_message_id = $_GET['last_message_id'] ?? 0;

        if (!$sender_id || !$recipient_id) {
            echo json_encode(['success' => false, 'error' => 'Sender or Recipient ID missing.']);
            exit;
        }

        $query = "SELECT id, message, sender_id, timestamp 
                  FROM chat_messages 
                  WHERE ((sender_id = ? AND recipient_id = ?) 
                      OR (sender_id = ? AND recipient_id = ?))
                      AND id > ? 
                  ORDER BY timestamp ASC";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('iiiii', $sender_id, $recipient_id, $recipient_id, $sender_id, $last_message_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            echo json_encode(['success' => true, 'messages' => $messages]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database query failed.']);
        }
        break;

    case 'sendMessage':
        // Handle sending messages
        $message = $_POST['message'] ?? '';
        $recipient_id = $_POST['recipient_id'] ?? null;
        $order_id = $_POST['order_id'] ?? null;

        if (!$message || !$recipient_id) {
            echo json_encode(['success' => false, 'error' => 'Message or recipient missing.']);
            exit;
        }

        if (!$order_id) {
            // Get the most recent order if not passed
            $query = "SELECT order_id FROM orders 
                      WHERE user_id = ? 
                        AND status IN ('Checked Out', 'Pending', 'Delivering') 
                      ORDER BY order_date DESC LIMIT 1";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    echo json_encode(['success' => false, 'error' => 'No active orders found.']);
                    exit;
                }

                $order = $result->fetch_assoc();
                $order_id = $order['order_id'];
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to retrieve active orders.']);
                exit;
            }
        }

        // Validate order status
        $query = "SELECT status FROM orders WHERE order_id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid order ID.']);
                exit;
            }

            $stmt->bind_result($order_status);
            $stmt->fetch();

            if ($order_status === 'Completed') {
                echo json_encode(['success' => false, 'error' => 'Order is completed; cannot send message.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to validate order ID.']);
            exit;
        }

        // Insert the new message
        $query = "INSERT INTO chat_messages 
                  (message, sender_id, recipient_id, timestamp, order_id, is_read, type) 
                  VALUES (?, ?, ?, NOW(), ?, 0, ?)";
        $message_type = ($_SESSION['user_id'] == $recipient_id) ? 'seller' : 'buyer';

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('siiis', $message, $_SESSION['user_id'], $recipient_id, $order_id, $message_type);
            if ($stmt->execute()) {
                // Fetch the new message data for immediate display
                $newMessage = [
                    'id' => $stmt->insert_id,
                    'message' => $message,
                    'sender_id' => $_SESSION['user_id'],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                echo json_encode(['success' => true, 'message' => $newMessage]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to send message.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to insert new message.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
?>