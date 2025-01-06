<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is logged in and is an admin (isAdmin = 1 or 2)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['isAdmin'], [1, 2])) {
    header("Location: ../login.php");
    exit;
}

// Check if store_id is provided and valid
if (isset($_GET['store_id']) && is_numeric($_GET['store_id'])) {
    $store_id = (int)$_GET['store_id'];  // Ensure it's an integer

    // Prepare and execute the delete statement
    $sql = "DELETE FROM stores WHERE store_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $store_id);
    $stmt->execute();

    // Check if deletion was successful
    if ($stmt->affected_rows > 0) {
        // Redirect back to the stores page after deletion
        header("Location: ../admin/admin_store.php");
        exit;
    } else {
        echo "Error: Unable to delete store.";
    }

    // Close statement
    $stmt->close();
} else {
    echo "Error: Store ID not provided or invalid.";
}

// Close connection
$conn->close();
?>
