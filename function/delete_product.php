<?php
session_start();
require_once '../connection/connection.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Check if the product ID is valid
    if ($product_id > 0) {
        $query = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        if ($query) {
            $query->bind_param("i", $product_id);
            if ($query->execute()) {
                header('Location: ../seller/seller_product.php?status=deleted');
                exit;
            } else {
                echo "Error executing query: " . $query->error;
            }
            $query->close();
        } else {
            echo "Error preparing query: " . $conn->error;
        }
    } else {
        echo "Invalid product ID.";
    }
} else {
    echo "Product ID not set.";
}
?>