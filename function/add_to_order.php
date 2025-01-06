<?php
session_start();
require_once '../connection/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "You need to log in to add products to the cart.";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']); // Ensure it's an integer
    $quantity = 1; // Default quantity
    $user_id = $_SESSION['user_id'];

    // Check if the product exists
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Product exists, add to cart
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + ?"; // Update quantity if item already in cart
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity); // Add quantity if already exists

        if ($stmt->execute()) {
            $_SESSION['message'] = "Product added to cart successfully.";
        } else {
            $_SESSION['message'] = "Failed to add product to cart: " . htmlspecialchars($stmt->error); // Include error details for debugging
        }
    } else {
        $_SESSION['message'] = "Product not found.";
    }

    $stmt->close();
} else {
    $_SESSION['message'] = "Invalid request.";
}

// Redirect to the cart page for editing
header("Location: ../order.php"); // Change to redirect to order.php if that's your cart editing page
exit;
?>
