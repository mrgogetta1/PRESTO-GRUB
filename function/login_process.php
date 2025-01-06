<?php
session_start();
require_once '../connection/connection.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both email and password.";
        header("Location: ../login.php");
        exit();
    }

    // Prepare and execute query to fetch user
    $stmt = $conn->prepare("SELECT id, password_hash, isAdmin, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password, $isAdmin, $username);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['isAdmin'] = $isAdmin;
            $_SESSION['username'] = $username;

            // Update the is_active field to 1
            $update_active_query = "UPDATE users SET is_active = 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_active_query);
            $update_stmt->bind_param("i", $user_id);
            if (!$update_stmt->execute()) {
                // Log the error or notify an admin if needed
                error_log("Failed to update is_active for user ID $user_id");
            }
            $update_stmt->close();

            // Redirect based on user role
            if ($isAdmin == 1) {
                header("Location: ../seller/seller.php"); // Seller
            } elseif ($isAdmin == 2) {
                header("Location: ../admin/admin_panel.php"); // Admin
            } else {
                header("Location: ../index.php"); // Regular user
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid email or password.";
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Invalid email or password.";
        header("Location: ../login.php");
        exit();
    }
} else {
    // Redirect if not a POST request
    header("Location: ../login.php");
    exit();
}
?>