<?php
session_start();
require_once '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input fields
    $storeName = isset($_POST['store_name']) ? $_POST['store_name'] : '';
    $storeDescription = isset($_POST['store_description']) ? $_POST['store_description'] : '';
    $storeContact = isset($_POST['store_contact']) ? $_POST['store_contact'] : '';
    $storeLocation = isset($_POST['store_location']) ? $_POST['store_location'] : '';

    // Image upload handling
    $storeImage = '';
    if (isset($_FILES['store_image']) && $_FILES['store_image']['error'] == UPLOAD_ERR_OK) {
        $storeImage = $_FILES['store_image']['name'];
        move_uploaded_file($_FILES['store_image']['tmp_name'], "C:/xampp/htdocs/3/store_images/" . $storeImage);
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO stores (store_name, store_description, store_contact, store_location, store_image, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $storeName, $storeDescription, $storeContact, $storeLocation, $storeImage, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo "Store added successfully!";
    } else {
        echo "Error adding store: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
