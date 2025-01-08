<?php
// Include PHPMailer and Exception classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// delivery_form.php

// Start session and include database connection
session_start();
require_once 'connection/connection.php';

// Ensure PHPMailer is loaded
require 'vendor/autoload.php';  // Change path if using a custom directory for PHPMailer's autoload

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location = 'login.php';</script>";
    exit;
}

if (isset($_POST['buy_now'])) {
    $product_id = $_POST['product_id'];
    $variant_id = $_POST['variant_id'] ?? null;
    $quantity = $_POST['quantity'];
    
    // Save order info in session
    $_SESSION['cart_items'] = [
        [
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'quantity'   => $quantity
        ]
    ];

    header('Location: delivery_form.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['cart_items']) || empty($_SESSION['cart_items'])) {
    echo "<script>alert('Cart items are missing. Please retry checkout.'); window.location = 'order.php';</script>";
    exit;
}
$cart_items = $_SESSION['cart_items'];

// Fetch user details including first_name and last_name
$user_query = "SELECT student_number, first_name, last_name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $student_number = $user['student_number'];
    $receiver_name = $user['first_name'] . ' ' . $user['last_name']; // Merge first and last name
    $customer_email = $user['email']; // Store customer email for sending notification
} else {
    echo "<script>alert('User not found.'); window.location = 'index.php';</script>";
    exit;
}

// Retrieve product and variant details from the session
$product_id = isset($_SESSION['cart_items'][0]['product_id']) ? $_SESSION['cart_items'][0]['product_id'] : null;
$variant_id = isset($_SESSION['cart_items'][0]['variant_id']) ? $_SESSION['cart_items'][0]['variant_id'] : 0; // Optional, 0 if not selected
$quantity = isset($_SESSION['cart_items'][0]['quantity']) ? $_SESSION['cart_items'][0]['quantity'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    $total_price = 0;

    // Fetch product details
    $product_query = "SELECT name, price FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();

    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        $base_price = $product['price'];

        // Adjust price if a variant is selected
        if ($variant_id > 0) {
            $variant_query = "SELECT price, variant_name, sku FROM product_variants WHERE variant_id = ?";
            $variant_stmt = $conn->prepare($variant_query);
            $variant_stmt->bind_param("i", $variant_id);
            $variant_stmt->execute();
            $variant_result = $variant_stmt->get_result();

            if ($variant_result->num_rows > 0) {
                $variant = $variant_result->fetch_assoc();
                $base_price += $variant['price'];
                $variant_name = $variant['variant_name'];
                $sku = $variant['sku'];
            } else {
                // Handle invalid variant gracefully
                echo "<script>alert('Invalid variant selected.'); window.location = 'view_product.php?product_id=$product_id';</script>";
                exit;
            }
        }

        $total_price = $base_price * $quantity;
    } else {
        echo "<script>alert('Product not found.'); window.location = 'index.php';</script>";
        exit;
    }

    // Insert order into database
    $insert_order_query = "INSERT INTO orders (user_id, product_id, quantity, total_price, room_number, student_number, receiver_name, status, order_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'Checked Out', NOW())";
    $stmt = $conn->prepare($insert_order_query);
    $stmt->bind_param("iiidsss", $user_id, $product_id, $quantity, $total_price, $room_number, $student_number, $receiver_name);

    if ($stmt->execute()) {
        $order_id = $conn->insert_id; // Capture the inserted order's ID

        // Fetch the seller's user ID and email
        $seller_id_query = "SELECT s.user_id AS seller_id, u.email AS seller_email FROM products p 
                            JOIN stores s ON p.store_id = s.store_id 
                            JOIN users u ON s.user_id = u.id 
                            WHERE p.product_id = ?";
        $seller_stmt = $conn->prepare($seller_id_query);
        $seller_stmt->bind_param("i", $product_id);
        $seller_stmt->execute();
        $seller_result = $seller_stmt->get_result();

        if ($seller_result->num_rows > 0) {
            $seller = $seller_result->fetch_assoc();
            $seller_email = $seller['seller_email'];
            $seller_id = $seller['seller_id'];

            // Insert notification for the seller
            $message = "New order placed:\nProduct ID: $product_id\nOrder ID: $order_id\nQuantity: $quantity\nTotal Price: ₱$total_price.";
            $notification_query = "INSERT INTO notifications (user_id, message, created_at) 
                                   VALUES (?, ?, NOW())";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_stmt->bind_param("is", $seller_id, $message);
            $notification_stmt->execute();

            // Send email to the customer and seller
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'raphiel322@gmail.com'; // SMTP username
                $mail->Password = 'hpzu mfan kzsk hdyj'; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Sender
                $mail->setFrom('raphiel322@gmail.com', 'Presto Grub');

                // Recipient: Customer
                $mail->addAddress($customer_email, $receiver_name); // Add customer email

                // Recipient: Seller
                $mail->addAddress($seller_email, 'Store Owner'); // Add seller email

                $mail->isHTML(true);
                $mail->Subject = 'New Order Confirmation';
                $mail->Body    = "
                    <h2>Order Confirmation</h2>
                    <p>Dear $receiver_name,</p>
                    <p>Thank you for your purchase. Your order (Order ID: $order_id) has been confirmed.</p>
                    <p><strong>Product Name:</strong> {$product['name']}</p>
                    <p><strong>Quantity:</strong> $quantity</p>
                    <p><strong>Total Price:</strong> ₱$total_price</p>
                    <p><strong>Payment Method:</strong> Cash on Delivery</p>
                    " . ($variant_name ? "<p><strong>Variant:</strong> $variant_name (SKU: $sku)</p>" : "<p><strong>SKU:</strong> $sku</p>") . "
                    <p>We will notify you once your order has been shipped.</p>
                    <p>Best regards,<br>Presto Grub</p>
                ";

                $mail->send();
                echo "<script>alert('Order confirmed, emails sent, and seller notified!'); window.location = 'order_status.php';</script>";

            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "<script>alert('Order placed, but seller not found.');</script>";
        }
    } else {
        echo "<script>alert('Error placing order.');</script>";
    }
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
            background: linear-gradient(
    45deg,
    #103687,
    #186439
  ); /* Single background for the entire page */
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
        .btn {
        margin-top: 10px; /* Add some space above the button */
    }

    button[type="submit"] {
        margin-bottom: 10px; /* Add some space below the submit button */
    }
    </style>
</head>
<body>
    <div class="container">
        <h2>Delivery Form</h2>

        <form method="POST">
            <label for="room_number">Room Number:</label>
            <input type="text" name="room_number" id="room_number" placeholder="Enter room number" required>

            <label for="student_number">Student Number:</label>
            <input type="text" name="student_number" id="student_number" value="<?php echo htmlspecialchars($student_number); ?>" readonly>

            <label for="receiver_name">Receiver Name:</label>
            <input type="text" name="receiver_name" id="receiver_name" value="<?php echo htmlspecialchars($receiver_name); ?>" readonly>

            <button type="submit">Place Order</button>
        </form>
    </div>
</body>
</html>
