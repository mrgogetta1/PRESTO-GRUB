<?php
session_start();
require_once '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $storeId = $_POST['store_id'];
    $storeName = $_POST['store_name'];
    $storeDescription = $_POST['store_description'];
    $storeContact = $_POST['store_contact'];
    $storeLocation = $_POST['store_location'];

    // Check if a new image is being uploaded
    if (isset($_FILES['store_image']) && $_FILES['store_image']['error'] == UPLOAD_ERR_OK) {
        $storeImage = $_FILES['store_image']['name'];
        move_uploaded_file($_FILES['store_image']['tmp_name'], "C:/xampp/htdocs/3/store_images/" . $storeImage);
        $stmt = $conn->prepare("UPDATE stores SET store_name=?, store_description=?, store_contact=?, store_location=?, store_image=? WHERE store_id=?");
        $stmt->bind_param("sssssi", $storeName, $storeDescription, $storeContact, $storeLocation, $storeImage, $storeId);
    } else {
        // No new image uploaded, keep the current image
        $stmt = $conn->prepare("UPDATE stores SET store_name=?, store_description=?, store_contact=?, store_location=? WHERE store_id=?");
        $stmt->bind_param("ssssi", $storeName, $storeDescription, $storeContact, $storeLocation, $storeId);
    }

    if ($stmt->execute()) {
        echo "Store updated successfully!";
    } else {
        echo "Error updating store: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
