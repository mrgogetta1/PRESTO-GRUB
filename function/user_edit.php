<?php
session_start();
include '../connection/connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 2) {
    header("Location: login.php");
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $user_address = $_POST['user_address'];
    $contact_no = $_POST['contact_no'];
    $course = $_POST['course'];
    $section = $_POST['section'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Validate the input (you can add more validation as needed)
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: users.php");
        exit;
    }

    // Prepare the SQL query to update user data
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, user_address=?, contact_no=?, course=?, section=?, username=?, isAdmin=? WHERE id=?");
    $stmt->bind_param("ssssssssii", $first_name, $last_name, $email, $user_address, $contact_no, $course, $section, $username, $role, $user_id);

    // Execute the query and check if successful
    if ($stmt->execute()) {
        $_SESSION['success'] = "User information updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating user information.";
    }

    // Redirect back to the users page
    header("Location: users.php");
    exit;
}
?>
