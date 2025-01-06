<?php
session_start();
require_once '../connection/connection.php';

// Directory path
$targetDir = 'uploads/';

// Check if the directory doesn't exist
if (!is_dir($targetDir)) {
    // Create the directory
    if (!mkdir($targetDir)) {
        // If unable to create directory, show error message
        die('Error: Unable to create directory');
    }
}

// Check if a file was uploaded
if ($_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // File details
    $fileName = basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file already exists
    $fileCount = 1;
    while (file_exists($targetFile)) {
        $fileName = pathinfo($_FILES["image"]["name"], PATHINFO_FILENAME) . "_$fileCount.$imageFileType";
        $targetFile = $targetDir . $fileName;
        $fileCount++;
    }

    // Upload file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        // Insert product details into the database
        $sql = "INSERT INTO products (user_id, name, description, price, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issds", $_SESSION['user_id'], $name, $description, $price, $targetFile);
        
        if ($stmt->execute()) {
            echo "Product added successfully.";
        } else {
            echo "Error adding product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo "Error uploading file.";
}
$conn->close();
?>
