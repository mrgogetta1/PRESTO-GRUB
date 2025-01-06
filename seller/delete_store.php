<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in or is not an admin
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    echo "Unauthorized access.";
    exit;
}

if (isset($_POST['store_id'])) {
    $store_id = $_POST['store_id'];

    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Delete the store
    $deleteStoreQuery = $conn->prepare("DELETE FROM stores WHERE store_id = ?");
    $deleteStoreQuery->bind_param("i", $store_id);

    if ($deleteStoreQuery->execute()) {
        echo "Store deleted successfully.";
    } else {
        echo "Error deleting store: " . $conn->error;
    }

    $deleteStoreQuery->close();

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
}
?>
