<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page
    exit; // Prevent further execution of the script
}

// Include database connection
include '../connection/connection.php';

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize
    $first_name = isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : null;
    $last_name = isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : null;
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $contact = isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : null;
    $address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : null;

    // Prepare the SQL statement for user creation
    $sql = "INSERT INTO users (first_name, last_name, email, password_hash, contact, address) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if the statement preparation was successful
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password, $contact, $address);

    // Execute the prepared statement
    if ($stmt->execute()) {
        // User created successfully
        echo "User created successfully.";
    } else {
        // Error creating user
        echo "Error creating user: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
}

// Close database connection
$conn->close();
?>
