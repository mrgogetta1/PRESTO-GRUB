<?php
// auth.php

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check login and redirect based on role
function check_login_and_redirect() {
    // If user ID is not set in the session, redirect to login
    if (!isset($_SESSION['user_id'])) {
        // Allow access to pages that don't require login
        $public_pages = ['login.php', 'register.php', 'index.php']; // Add other public pages if any
        $current_page = basename($_SERVER['PHP_SELF']);

        if (!in_array($current_page, $public_pages)) {
            header("Location: login.php");
            exit();
        }
    } else {
        // User is logged in, redirect based on role if accessing unauthorized pages
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
        $current_page = basename($_SERVER['PHP_SELF']);

        if ($role == 0) { // Normal User
            // Normal users should not access seller or admin panels
            if ($current_page == 'seller.php' || $current_page == 'admin_panel.php') {
                header("Location: index.php");
                exit();
            }
        } elseif ($role == 1) { // Seller
            // Sellers should not access index or admin panels
            if ($current_page == 'index.php' || $current_page == 'admin_panel.php') {
                header("Location: seller.php");
                exit();
            }
        } elseif ($role == 2) { // Admin
            // Admins should not access index or seller panels
            if ($current_page == 'index.php' || $current_page == 'seller.php') {
                header("Location: admin_panel.php");
                exit();
            }
        } else {
            // Undefined role, log out for security
            header("Location: logout.php");
            exit();
        }
    }
}

// Call the function to enforce access control
check_login_and_redirect();
?>
