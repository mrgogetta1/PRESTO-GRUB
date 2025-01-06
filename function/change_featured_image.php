<?php
require_once '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['featured_product'];
    $action = $_POST['action'];

    // Handle the 'change' action (file upload)
    if ($action == 'change' && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name'];
        $target_dir = "../productimg/";
        $target_file = $target_dir . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);

        $update_query = "UPDATE products SET image = ? WHERE product_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('si', $image, $product_id);
        $stmt->execute();

        echo "Image updated successfully!";
    }

    // Handle the 'delete' action
    elseif ($action == 'delete') {
        $delete_query = "UPDATE products SET image = NULL WHERE product_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('i', $product_id);
        $stmt->execute();

        echo "Image deleted successfully!";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to admin panel or wherever appropriate
    header('Location: ../admin_panel.php');
    exit();
}
?>
