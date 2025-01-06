<?php
session_start();
require_once '../connection/connection.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Update the is_active field to 0
    $update_active_query = "UPDATE users SET is_active = 0 WHERE id = ?";
    $update_stmt = $conn->prepare($update_active_query);
    $update_stmt->bind_param("i", $user_id);

    if (!$update_stmt->execute()) {
        // Log the error for debugging
        error_log("Failed to update is_active to 0 for user ID $user_id");
    }

    $update_stmt->close();
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home page
header("Location: ../index.php");
exit();
?>