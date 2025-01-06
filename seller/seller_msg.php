<?php 
session_start();
require_once '../connection/connection.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Set the timezone to the Philippines
date_default_timezone_set('Asia/Manila');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin_or_seller = $_SESSION['isAdmin'] == 1 || $_SESSION['isAdmin'] == 2; // Admin or Seller
$is_buyer = $_SESSION['isAdmin'] == 0;

// Validate and sanitize receiver_id and order_id
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : null;
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

// Clear old chats (older than 2 hours based on chat start time)
$conn->query("DELETE FROM chat_messages WHERE timestamp < NOW() - INTERVAL 2 HOUR");

// Initialize $chat_messages and recent users as empty arrays
$chat_messages = [];
$recent_chat_users = [];

// Fetch recent chat users for the logged-in user
$query = "
    SELECT DISTINCT u.id AS user_id, u.email 
    FROM chat_messages cm
    JOIN users u ON (cm.sender_id = u.id OR cm.recipient_id = u.id)
    WHERE cm.sender_id = ? OR cm.recipient_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if ($row['user_id'] != $user_id) {
        $recent_chat_users[] = $row;
    }
}
$stmt->close();

// Fetch chat messages if receiver_id is set
if (!empty($receiver_id)) {
    $stmt = $conn->prepare("SELECT cm.sender_id, cm.recipient_id, cm.message, cm.timestamp, cm.order_id 
                            FROM chat_messages cm 
                            WHERE (cm.sender_id = ? AND cm.recipient_id = ?) 
                               OR (cm.sender_id = ? AND cm.recipient_id = ?) 
                            ORDER BY cm.timestamp ASC");
    $stmt->bind_param('iiii', $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $chat_messages[] = $row;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['receiver_id'])) {
    $message = trim($_POST['message']);
    $receiver_id = (int)$_POST['receiver_id'];
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;

    if (!empty($receiver_id) && !empty($message)) {
        if ($order_id) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE order_id = ?");
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count === 0) {
                echo json_encode(['error' => 'Invalid order_id provided.']);
                exit;
            }
        }

        if ($order_id) {
            $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, recipient_id, message, timestamp, is_read, order_id, type) 
                                    VALUES (?, ?, ?, NOW(), 0, ?, 'manual')");
            $stmt->bind_param('iisi', $user_id, $receiver_id, $message, $order_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, recipient_id, message, timestamp, is_read, order_id, type) 
                                    VALUES (?, ?, ?, NOW(), 0, NULL, 'manual')");
            $stmt->bind_param('iis', $user_id, $receiver_id, $message);
        }

        if ($stmt->execute()) {
            $notificationQuery = $conn->prepare("INSERT INTO notifications (user_id, message, is_read) 
                                                 VALUES (?, ?, 0)");
            $notificationMessage = "You have a new reply from the seller regarding your order.";
            $notificationQuery->bind_param('is', $receiver_id, $notificationMessage);
            $notificationQuery->execute();

            echo json_encode([
                'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
                'timestamp' => date('Y-m-d H:i')
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send message.']);
        }
        $stmt->close();
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Receiver ID and message cannot be empty.']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Chat</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; }
        .header { background-color: #343a40; color: #fff; padding: 20px; margin-bottom: 20px; text-align: center; }
        .menu { background-color: #fff; padding: 10px; display: flex; justify-content: space-around; margin-bottom: 20px; }
        .chat-container { max-width: 600px; margin: 0 auto; }
        .chat-box { height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background-color: #f8f9fa; }
        .message { padding: 10px; margin-bottom: 10px; border-radius: 10px; }
        .message.sender { background-color: #d1e7dd; }
        .message.receiver { background-color: #f8d7da; }
        .message-time { font-size: 0.8rem; color: #6c757d; text-align: right; }
        .recent-chats { padding: 10px; border: 1px solid #ccc; max-height: 300px; overflow-y: scroll; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Seller Chat</h1>
    </div>
    <div class="menu">
        <a href="seller.php">Dashboard</a>
        <a href="seller_msg.php">Chat</a>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="recent-chats">
                <?php if (!empty($recent_chat_users)): ?>
                    <?php foreach ($recent_chat_users as $user): ?>
                        <div class="chat-user">
                            <a href="seller_msg.php?receiver_id=<?= htmlspecialchars($user['user_id'], ENT_QUOTES, 'UTF-8'); ?>&order_id=<?= $order_id; ?>">
                                <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No recent chats.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-8">
            <?php if ($receiver_id): ?>
                <?php
                $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->bind_param('i', $receiver_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $recipient = $result->fetch_assoc();
                $recipient_email = htmlspecialchars($recipient['email'] ?? 'Unknown User', ENT_QUOTES, 'UTF-8');
                ?>
                <h4>Chat with <?= $recipient_email; ?></h4>
            <?php endif; ?>
            <div class="chat-box" id="messages">
                <?php foreach ($chat_messages as $msg): ?>
                    <div class="message <?= $msg['sender_id'] == $user_id ? 'sender' : 'receiver' ?>">
                        <p><?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="message-time"><?= date('Y-m-d H:i', strtotime($msg['timestamp'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <form id="chatForm">
                <textarea id="messageInput" class="form-control" placeholder="Type your message here..."></textarea>
                <button type="button" id="sendMessage" class="btn btn-primary mt-2">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sendButton = document.getElementById('sendMessage');
    const messageInput = document.getElementById('messageInput');
    const messagesContainer = document.getElementById('messages');

    // Poll for new messages every 3 seconds
    setInterval(function() {
        fetch('fetch_msg.php?receiver_id=<?= $receiver_id; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.new_messages) {
                    data.new_messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message', msg.sender_id == <?= $user_id ?> ? 'sender' : 'receiver');
                        messageDiv.innerHTML = `<p>${msg.message}</p><p class="message-time">${msg.timestamp}</p>`;
                        messagesContainer.appendChild(messageDiv);
                        messagesContainer.scrollTop = messagesContainer.scrollHeight; // Scroll to bottom
                    });
                }
            });
    }, 3000);

    sendButton.addEventListener('click', function() {
        const message = messageInput.value.trim();
        if (message === '') {
            alert('Please type a message');
            return;
        }

        const formData = new FormData();
        formData.append('message', message);
        formData.append('receiver_id', <?= $receiver_id; ?>);
        formData.append('order_id', <?= $order_id ? $order_id : 'null'; ?>);

        fetch('seller_msg.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', 'sender');
                messageDiv.innerHTML = `<p>${data.message}</p><p class="message-time">${data.timestamp}</p>`;
                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight; // Scroll to bottom
                messageInput.value = ''; // Clear the input field
            }
        })
        .catch(error => {
            alert('An error occurred while sending the message.');
        });
    });
});
</script>
</body>
</html>
