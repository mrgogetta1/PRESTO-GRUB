<?php
session_start();

// Include database connection
include '../connection/connection.php';
require_once 'auth.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the 'id' key is set in the session
    if (!isset($_SESSION['id'])) {
        echo "Error: User ID not set in session.";
        exit;
    }

    // Debug: Print the session ID
    var_dump($_SESSION['id']);
    // After successful login, retrieve the user's ID from the database
$user_id = // retrieve user's ID from the database based on email or username

// Set the user's ID in the session
$_SESSION['id'] = $user_id;


    // Retrieve form data and sanitize
    $first_name = isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : null;
    $last_name = isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : null;
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;
    $contact = isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : null;
    $address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : null;
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Prepare the SQL statement
    $sql = "UPDATE users SET ";
    $params = array();
    $types = "";

    // Build the SQL query and parameters based on provided fields
    if (!is_null($first_name)) {
        $sql .= "first_name=?, ";
        $params[] = $first_name;
        $types .= "s";
    }
    if (!is_null($last_name)) {
        $sql .= "last_name=?, ";
        $params[] = $last_name;
        $types .= "s";
    }
    if (!is_null($email)) {
        $sql .= "email=?, ";
        $params[] = $email;
        $types .= "s";
    }
    if (!is_null($password)) {
        $sql .= "password_hash=?, ";
        $params[] = $password;
        $types .= "s";
    }
    if (!is_null($contact)) {
        $sql .= "contact=?, ";
        $params[] = $contact;
        $types .= "s";
    }
    if (!is_null($address)) {
        $sql .= "address=?, ";
        $params[] = $address;
        $types .= "s";
    }

    // Remove the trailing comma and space from SQL query
    $sql = rtrim($sql, ", ");

    // Add the WHERE clause to update only the current user's record
    $sql .= " WHERE id=?";

    // Add the user ID to the parameters array
    $params[] = $_SESSION['id'];
    $types .= "i";

    // Debug: Print the SQL query
    var_dump($sql);

    // Prepare and bind the parameters
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }
    $stmt->bind_param($types, ...$params); // Using variable unpacking to pass an array of parameters

    // Execute the SQL statement
    if ($stmt->execute()) {
        // Account details updated successfully
        echo "Account details updated successfully.";
    } else {
        echo "Error updating account details: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
