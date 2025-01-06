<?php
session_start();
require_once '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = $_POST['store_id'];
    $store_name = $_POST['store_name'];
    $store_description = $_POST['store_description'];
    $store_contact = $_POST['store_contact'];
    $store_location = $_POST['store_location'];
    $imageUploadSuccess = false;

    // Handle image upload
    if (isset($_FILES['store_image']) && $_FILES['store_image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "store_images/"; // Specify your image upload directory
        $imageFileType = strtolower(pathinfo($_FILES['store_image']['name'], PATHINFO_EXTENSION));
        $targetFile = $targetDir . uniqid("store_") . '.' . $imageFileType;

        // Check if the file is an image
        $check = getimagesize($_FILES['store_image']['tmp_name']);
        if ($check !== false) {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES['store_image']['tmp_name'], $targetFile)) {
                $imageUploadSuccess = true;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Uploaded file is not an image.']);
            exit;
        }
    }

    // Prepare the update query
    if ($imageUploadSuccess) {
        // If a new image was uploaded, update the image path in the database
        $updateQuery = $conn->prepare("UPDATE stores SET store_name = ?, store_description = ?, store_contact = ?, store_location = ?, store_image = ? WHERE store_id = ?");
        $updateQuery->bind_param("sssssi", $store_name, $store_description, $store_contact, $store_location, $targetFile, $store_id);
    } else {
        // If no new image was uploaded, do not change the image path
        $updateQuery = $conn->prepare("UPDATE stores SET store_name = ?, store_description = ?, store_contact = ?, store_location = ? WHERE store_id = ?");
        $updateQuery->bind_param("ssssi", $store_name, $store_description, $store_contact, $store_location, $store_id);
    }

    // Execute the update query
    if ($updateQuery->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
}
?>
