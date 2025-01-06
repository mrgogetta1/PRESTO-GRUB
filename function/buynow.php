<?php
session_start();
require_once 'connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $total_price = $_POST['total_price'];
    $payment_method = $_POST['payment_method'];

    // When user selects a product
    $_SESSION['temp_order'] = [
        'user_id' => $user_id,           // User ID (this should be available)
        'product_id' => $product_id,     // Ensure the correct product ID is set
        'quantity' => 1,                 // Or whatever quantity was selected
        'total_price' => $total_price    // Calculated total price
    ];
    header('Location: ../delivery_form.php');  // Redirect to the delivery form
    exit();

}
?>
