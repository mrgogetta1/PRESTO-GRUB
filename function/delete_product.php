<?php
session_start();
require_once '../connection/connection.php';

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $query = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $query->bind_param("i", $product_id);
    if ($query->execute()) {
        header('Location: seller_product.php?status=deleted');
        exit;
    } else {
        echo "Error deleting product.";
    }
}
?>
