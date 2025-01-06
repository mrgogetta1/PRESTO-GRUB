<?php
require_once '../connection/connection.php';

// Check if the necessary fields are posted
if (isset($_POST['product_id']) && isset($_POST['size']) && isset($_POST['spicy_level'])) {
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];
    $spicy_level = $_POST['spicy_level'];
    $stock_quantity = isset($_POST['stock_quantity']) ? $_POST['stock_quantity'] : 0;

    // Insert variant into the database
    $query = $conn->prepare("INSERT INTO product_variants (product_id, size, spicy_level, stock_quantity) VALUES (?, ?, ?, ?)");
    $query->bind_param("issi", $product_id, $size, $spicy_level, $stock_quantity);

    if ($query->execute()) {
        echo "Variant added successfully.";
    } else {
        echo "Error: Could not add variant.";
    }
}
?>
