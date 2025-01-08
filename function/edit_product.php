<?php
session_start();
require_once '../connection/connection.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: ../login.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get product data from the form
    $product_id = $_POST['product_id'];
    $store_id = $_POST['store_id']; // Ensure store_id is retrieved
    $product_name = $_POST['edit_product_name'];
    $product_description = $_POST['edit_product_description'];
    $product_price = $_POST['edit_product_price'];
    $product_stock_quantity = $_POST['edit_stock_quantity'];
    $category_id = $_POST['category_id']; // Get the category_id from the form

    // Fetch the current product data to retain the existing image if no new image is uploaded
    $currentProductQuery = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $currentProductQuery->bind_param("i", $product_id);
    $currentProductQuery->execute();
    $currentProductResult = $currentProductQuery->get_result();
    $currentProduct = $currentProductResult->fetch_assoc();
    
    // Initialize product_image with existing image
    $product_image = $currentProduct['image'];

    // Optional: Handle file upload for product image
    if (isset($_FILES['edit_product_image']) && $_FILES['edit_product_image']['error'] == 0) {
        // Handle the image upload
        $product_image = $_FILES['edit_product_image']['name'];
        $target_dir = "C:/xampp/htdocs/phpprogram/2/productimg/"; // Change this to your local path
        move_uploaded_file($_FILES['edit_product_image']['tmp_name'], $target_dir . $product_image);
    }

    // Update the product in the database
    $stmt = $conn->prepare("UPDATE products SET store_id = ?, name = ?, description = ?, price = ?, stock_quantity = ?, image = ?, category_id = ? WHERE product_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("isssissi", $store_id, $product_name, $product_description, $product_price, $product_stock_quantity, $product_image, $category_id, $product_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . htmlspecialchars($stmt->error));
    }

    // Redirect or provide a success message
    header("Location: ../seller/seller_product.php");
    exit;
}
?>