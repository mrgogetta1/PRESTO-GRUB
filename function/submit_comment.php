<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_GET['product_id'];
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Verify user eligibility to comment
    $order_check_query = "SELECT COUNT(*) FROM orders WHERE user_id = $user_id AND product_id = $product_id AND status = 'complete'";
    $result = $conn->query($order_check_query);
    $has_completed_order = ($result && $result->fetch_row()[0] > 0);

    // Check if user has already commented
    $comment_check_query = "SELECT COUNT(*) FROM comments WHERE user_id = $user_id AND product_id = $product_id";
    $result = $conn->query($comment_check_query);
    $has_commented = ($result && $result->fetch_row()[0] > 0);

    if ($has_completed_order && !$has_commented) {
        // Insert the comment into the database
        $insert_query = "INSERT INTO comments (product_id, user_id, username, rating, comment, date_posted) VALUES ($product_id, $user_id, '{$_SESSION['username']}', $rating, '$comment', NOW())";
        
        if ($conn->query($insert_query) === TRUE) {
            echo "Comment added successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "You are not eligible to comment.";
    }
}
?>
