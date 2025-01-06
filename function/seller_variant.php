<?php
require_once '../connection/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
        $variant_name = isset($_POST['variant_name']) ? trim($_POST['variant_name']) : null;
        $stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : null;
        $sku = isset($_POST['sku']) ? trim($_POST['sku']) : null;
        $price = isset($_POST['price']) ? floatval($_POST['price']) : null;

        if (!$product_id || !$variant_name || $stock_quantity === null || !$sku || $price === null) {
            throw new Exception('Missing or invalid input fields.');
        }

        $stmt = $conn->prepare("INSERT INTO product_variants (product_id, variant_name, stock_quantity, sku, price) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare the SQL statement: ' . $conn->error);
        }

        $stmt->bind_param("isiss", $product_id, $variant_name, $stock_quantity, $sku, $price);
        if (!$stmt->execute()) {
            throw new Exception('Database error: ' . $stmt->error);
        }

        echo json_encode(['status' => 'success', 'message' => 'Variant added successfully.']);
    } catch (Exception $e) {
        error_log($e->getMessage()); // Log detailed error for debugging
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
        $conn->close();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
