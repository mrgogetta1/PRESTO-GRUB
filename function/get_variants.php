<?php
require_once '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = intval($_GET['product_id']);

    $stmt = $conn->prepare("SELECT DISTINCT variant_name FROM product_variants WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $variants = [];
    while ($row = $result->fetch_assoc()) {
        $variants[] = $row;
    }

    echo json_encode(['status' => 'success', 'variants' => $variants]);

    $stmt->close();
    $conn->close();
}
?>
