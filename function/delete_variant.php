<?php
require_once '../connection/connection.php';

if (isset($_GET['variant_id'])) {
    $variant_id = $_GET['variant_id'];

    // Delete variant from the database
    $query = $conn->prepare("DELETE FROM product_variants WHERE variant_id = ?");
    $query->bind_param("i", $variant_id);

    if ($query->execute()) {
        header("Location: ../seller_product.php?message=Variant deleted successfully");
    } else {
        echo "Error: Could not delete variant.";
    }
}
?>
