<?php
session_start();
require_once 'connection/connection.php';  // Ensure database connection is included

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the form was submitted for Add to Cart or Buy Now
    if (isset($_POST['add_to_cart']) || isset($_POST['buy_now'])) {
        // Retrieve product ID and quantity from the form
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        // Validate product ID and quantity
        if (!$product_id || $quantity < 1) {
            error_log("Invalid product ID or quantity: Product ID = $product_id, Quantity = $quantity");
            header('Location: error_page.php'); // Redirect to an error page or display an error message
            exit;
        }

        // Debug: Log received values
        error_log("Adding to cart: Product ID = $product_id, Quantity = $quantity");

        // Ensure the cart session exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add product to the cart or update quantity if already in cart
        if (isset($_SESSION['cart'][$product_id])) {
            // Product already in cart, update the quantity
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            error_log("Updated quantity for Product ID $product_id. New Quantity: " . $_SESSION['cart'][$product_id]['quantity']);
        } else {
            // Product not in cart, add it
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
            ];
            error_log("Added Product ID $product_id to cart with Quantity: $quantity");
        }

        // If the Buy Now button was clicked, redirect to the checkout page
        if (isset($_POST['buy_now'])) {
            error_log("Redirecting to order_status.php for Buy Now.");
            header('Location: order_status.php');  // Redirect to checkout page
            exit;
        } else {
            // If Add to Cart was clicked, redirect to the cart page
            error_log("Redirecting to order.php after adding to cart.");
            header('Location: order.php');  // Redirect to the cart page
            exit;
        }
    } else {
        // Handle invalid POST request
        error_log("Invalid POST request to add_to_cart.php.");
        header('Location: error_page.php'); // Redirect to an error page or display an error message
        exit;
    }
} else {
    // Handle invalid access method
    error_log("Invalid access method to add_to_cart.php. Only POST is allowed.");
    header('Location: error_page.php'); // Redirect to an error page or display an error message
    exit;
}
