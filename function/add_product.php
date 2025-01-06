<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: ../login.php");
    exit;
}

// Handle the form submission for adding a new product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_id = $_POST['store_id'];
    $name = $_POST['add_product_name'];
    $description = $_POST['add_product_description'];
    $price = $_POST['add_product_price'];
    $image = $_FILES['add_product_image'];
    $category = $_POST['category']; // Get the category from the form

    // Validate that the store ID exists
    $storeCheck = $conn->prepare("SELECT store_id FROM stores WHERE store_id = ?");
    $storeCheck->bind_param("i", $store_id);
    $storeCheck->execute();
    $storeCheckResult = $storeCheck->get_result();

    if ($storeCheckResult->num_rows === 0) {
        die("Error: Store ID does not exist. Please check the store ID you provided.");
    }

    // Process image upload
    $target_dir = "../productimg/"; // Relative path from the current script
    $target_file = $target_dir . basename($image["name"]);

    if (move_uploaded_file($image["tmp_name"], $target_file)) {
        // Insert the product into the database, including the category
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, user_id, store_id, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsiss", $name, $description, $price, $image["name"], $_SESSION['user_id'], $store_id, $category);
        
        if ($stmt->execute()) {
            header('location: ../seller/seller_product.php');
            exit();
        } else {
            echo "Error adding product: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading image.";
    }

    $storeCheck->close();
    $conn->close();
}
?>
