<?php
session_start();
require_once '../connection/connection.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure product_id and status are set
    if (isset($_POST['product_id']) && isset($_POST['status'])) {
        $product_id = intval($_POST['product_id']);
        $status = intval($_POST['status']);

        // Prepare the SQL statement to update stock status
        $stmt = $conn->prepare("UPDATE products SET out_of_stock = ? WHERE product_id = ?");
        if ($stmt === false) {
            die(json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . htmlspecialchars($conn->error)]));
        }

        // Bind parameters and execute the statement
        $stmt->bind_param("ii", $status, $product_id);
        if (!$stmt->execute()) {
            die(json_encode(['status' => 'error', 'message' => 'Database execute failed: ' . htmlspecialchars($stmt->error)]));
        }

        // Successful update response
        echo json_encode(['status' => 'success', 'message' => 'Stock status updated successfully.']);
        exit;
    } else {
        // Handle the case where parameters are missing
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
        exit;
    }
}

// If the request method is not POST
echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
exit;
