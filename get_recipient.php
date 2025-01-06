<?php
require_once 'connection/connection.php';
session_start();

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'Order ID is required.']);
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in or role not set.']);
    exit;
}

$order_id = $_GET['order_id'];
$user_role = $_SESSION['role'];

if ($user_role == 0) { 
    $query = "SELECT p.user_id AS recipient_id FROM orders o 
              INNER JOIN products p ON o.product_id = p.product_id 
              WHERE o.order_id = ?";
} else if ($user_role == 1) {
    $query = "SELECT o.user_id AS recipient_id FROM orders o 
              WHERE o.order_id = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    echo json_encode(['success' => true, 'recipient_id' => $row['recipient_id']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Recipient not found.']);
}
?>