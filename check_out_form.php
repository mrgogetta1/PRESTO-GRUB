<?php
session_start();
require_once 'connection/connection.php';

// Check if product_id exists in session
if (!isset($_SESSION['product_id'])) {
    die("Error: Product ID is missing in the session.");
}

$product_id = $_SESSION['product_id'];
$quantity = $_SESSION['quantity'] ?? 1;

// Fetch product details
$product_query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Product not found.");
}
$product = $result->fetch_assoc();
$stmt->close();

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture form inputs
    $room_number = $_POST['room_number'];
    $student_id = $_POST['student_id'];
    $receiver_name = $_POST['receiver_name'];
    $total_price = $product['price'] * $quantity;

    // Insert order into orders table
    $order_query = "INSERT INTO orders (user_id, product_id, quantity, total_price, room_number, student_id, receiver_name, status, order_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param(
        "iiidiss", 
        $_SESSION['user_id'], 
        $product_id, 
        $quantity, 
        $total_price, 
        $room_number, 
        $student_id, 
        $receiver_name
    );

    if ($stmt->execute()) {
        // Get the order_id of the newly inserted order
        $order_id = $stmt->insert_id;

        // Fetch seller information (store_id)
        $store_query = "SELECT store_id FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($store_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $store_result = $stmt->get_result();
        $store = $store_result->fetch_assoc();
        $store_id = $store['store_id'];

        // Fetch seller's user_id based on store_id
        $seller_query = "SELECT user_id FROM stores WHERE store_id = ?";
        $stmt = $conn->prepare($seller_query);
        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        $seller_result = $stmt->get_result();
        $seller = $seller_result->fetch_assoc();
        $seller_id = $seller['user_id'];

        // Prepare message to send to seller
        $message = "New order received! Order ID: $order_id, Product: {$product['name']}, Quantity: $quantity, Total Price: ₱" . number_format($total_price, 2);

        // Insert message into chat_messages table
        $message_query = "INSERT INTO chat_messages (sender_id, recipient_id, message, timestamp, order_id) 
                          VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($message_query);
        $stmt->bind_param("iisi", $_SESSION['user_id'], $seller_id, $message, $order_id);

        if ($stmt->execute()) {
            // Redirect to Order Status page
            header("Location: order_status.php");
            exit;
        } else {
            echo "Error sending message to seller: " . $stmt->error;
        }
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url('uploads/yup.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            font-family: Arial, sans-serif; /* Unified font style */
            color: #333; /* Default text color for body */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 20px 30px;
        }

        h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #56ab2f, #4CAF50);
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #4CAF50, #56ab2f);
        }

        .summary {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .summary p {
            font-size: 14px;
            margin: 5px 0;
            color: #666;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Delivery Form</h2>

        <!-- Product Summary -->
        <div class="summary">
            <p><strong>Product:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
            <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
            <p><strong>Quantity:</strong> <?php echo $quantity; ?></p>
            <p><strong>Total:</strong> ₱<?php echo number_format($product['price'] * $quantity, 2); ?></p>
        </div>

        <!-- Delivery Form -->
        <form method="POST">
            <label for="room_number">Room Number:</label>
            <input type="text" name="room_number" id="room_number" placeholder="Enter room number" required>

            <label for="student_id">Student ID:</label>
            <input type="number" name="student_id" id="student_id" placeholder="Enter student ID" required>

            <label for="receiver_name">Receiver Name:</label>
            <input type="text" name="receiver_name" id="receiver_name" placeholder="Enter receiver name" required>

            <button type="submit">Place Order</button>
        </form>
    </div>
</body>
</html>