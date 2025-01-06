<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../connection/connection.php'; // Include the database connection file

// Display errors for development purposes
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Ensure user is logged in and is a seller or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: ../login.php");
    exit;
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

$orderStatus = ''; // Default value
// Pagination settings
$records_per_page = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base query and filters
$queryConditions = "WHERE (products.user_id = ? OR products.user_id IN 
    (SELECT store_id FROM stores WHERE user_id = ?))";

$params = [$user_id, $user_id];

// Apply filters if submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Filter by date
    if (!empty($_POST['order_date'])) {
        $orderDate = date('Y-m-d', strtotime($_POST['order_date']));
        $queryConditions .= " AND DATE(orders.order_date) = ?";
        $params[] = $orderDate;
    }

    // Filter by order status
    if (!empty($_POST['order_status'])) {
        $orderStatus = $_POST['order_status'];
        $queryConditions .= " AND orders.status = ?";
        $params[] = $orderStatus;
    }
}

// Count total orders query
$countQuery = "SELECT COUNT(*) AS total_orders 
               FROM orders 
               INNER JOIN products ON orders.product_id = products.product_id
               $queryConditions";

$countParams = $params; // Use the same $params for counting
$countTypes = str_repeat('s', count($countParams)); // Match types to $countParams

$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param($countTypes, ...$countParams); // Bind parameters dynamically
$countStmt->execute();
$totalOrders = $countStmt->get_result()->fetch_assoc()['total_orders'];
$totalPages = ceil($totalOrders / $records_per_page);

// Orders query
$queryOrders = "SELECT orders.*, 
                       users.first_name, users.last_name, users.course, users.section, 
                       users.email AS user_email, users.contact_no, 
                       products.name AS product_name, products.price, 
                       stores.store_name, 
                       orders.room_number, orders.student_number, orders.receiver_name 
                FROM orders
                INNER JOIN users ON orders.user_id = users.id
                INNER JOIN products ON orders.product_id = products.product_id
                INNER JOIN stores ON products.store_id = stores.store_id
                $queryConditions
                ORDER BY orders.order_date DESC
                LIMIT ?, ?";

// Add pagination to the query
$params[] = $offset; // Add offset
$params[] = $records_per_page; // Add limit
$types = str_repeat('s', count($params) - 2) . 'ii'; // Create dynamic types string for 's' for strings and 'i' for integers

$stmt = $conn->prepare($queryOrders);
$stmt->bind_param($types, ...$params); // Bind parameters dynamically
$stmt->execute();
$resultOrders = $stmt->get_result();


// Fetch the latest notifications for the seller
$notificationsQuery = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$notificationsStmt = $conn->prepare($notificationsQuery);
$notificationsStmt->bind_param("i", $user_id);
$notificationsStmt->execute();
$notificationsResult = $notificationsStmt->get_result();








/// Handle order status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update order status
        $updateStatusStmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $updateStatusStmt->bind_param("si", $status, $order_id);
        $updateStatusStmt->execute();

        // Get the buyer's user ID and email
        $orderQuery = $conn->prepare("SELECT user_id FROM orders WHERE order_id = ?");
        $orderQuery->bind_param("i", $order_id);
        $orderQuery->execute();
        $orderResult = $orderQuery->get_result();
        $orderData = $orderResult->fetch_assoc();
        $buyer_id = $orderData['user_id'];

        // Get the buyer's email address
        $buyerEmailQuery = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $buyerEmailQuery->bind_param("i", $buyer_id);
        $buyerEmailQuery->execute();
        $buyerResult = $buyerEmailQuery->get_result();
        $buyerData = $buyerResult->fetch_assoc();

        $buyer_email = $buyerData['email'];  // Correctly retrieve the email address

        // Insert notification
        $message = "Your order status has been updated to '$status'.";
        $created_at = date('Y-m-d H:i:s');
        $insertNotificationStmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, ?)");
        $insertNotificationStmt->bind_param("iss", $buyer_id, $message, $created_at);
        $insertNotificationStmt->execute();

        // Send email notification using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'raphiel322@gmail.com';
            $mail->Password = 'hpzu mfan kzsk hdyj'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('raphiel322@gmail.com', 'Presto Grub');
            $mail->addAddress($buyer_email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Order Status Updated';
            $mail->Body = "<p>Dear Customer,</p>
                            <p>Your order status has been updated to <strong>$status</strong>.</p>
                            <p>Thank you for shopping with us!</p>";

            $mail->send();

        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo, 3, 'error_log.txt');
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = "Order status successfully updated.";
        header("Location: seller_order.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction failed: " . $e->getMessage(), 3, 'error_log.txt');
        echo "Transaction failed: " . $e->getMessage();
    }
}


// Display success message
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']); // Clear the message after displaying it
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Seller Panel - Orders</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('uploads/yup.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Roboto', sans-serif;
            height: 95vh;
            
            
        }
        .header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .menu {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-around;
            padding: 10px;
        }
        .menu a {
            text-decoration: none;
            color: #343a40;
            font-weight: bold;
        }
        .menu a:hover {
            color: #007bff;
        }
        .content {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto; /* Fix table overflow */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap; /* Prevent content from breaking lines */
        }
        table th {
            background-color: #f1f1f1;
        }
        table td {
            word-wrap: break-word; /* Handle long text in cells */
        }
        .form-control, .btn {
            max-width: 100%; /* Prevent buttons or inputs from exceeding container */
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .notification-container {
            position: relative;
            display: inline-block;
        }

        .notification-button {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            position: relative;
            color: #343a40;
        }

        .notification-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 5px 8px;
            font-size: 0.8em;
            display: none;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 40px;
            background: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 250px;
            border-radius: 5px;
            z-index: 1000;
        }

        .notification-dropdown h6 {
            margin: 0;
            padding: 10px;
            background: #f1f1f1;
            border-bottom: 1px solid #ddd;
            font-size: 1em;
        }

        .notification-dropdown ul {
            list-style: none;
            margin: 0;
            padding: 0;
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-dropdown ul li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .notification-dropdown ul li:last-child {
            border-bottom: none;
        }

        .notification-dropdown ul li:hover {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .menu {
                flex-direction: column;
                align-items: center;
            }
            table th, table td {
                font-size: 0.9rem;
            }
            .form-row {
                flex-direction: column;
            }
            .form-row .col {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Presto Grub Seller Panel - Orders</h1>
        <form method="POST" action="../function/logout.php">
            <button type="submit" class="btn btn-primary btn-sm">Logout</button>
        </form>
    </div>

    <div class="menu">
        <a href="seller.php" onclick="showDashboard()">Dashboard</a>
        <a href="sellerstore.php" onclick="showStores()">Stores</a>
        <a href="seller_product.php" onclick="showProducts()">Products</a>
        <a href="seller_order.php" onclick="showOrders()">Orders</a>
        <a href="seller_report.php" onclick="showCompleteOrders()">Report</a>
        <a href="seller_msg.php" onclick="showChat()">Chat</a>
    
        <!-- Notification Bell -->
        <div class="notification-container">
            <button id="notificationBtn" class="notification-button">
                <i class="fas fa-bell"></i>
                <span id="notificationCount" class="notification-count">
                    <?php echo $notificationsResult ? $notificationsResult->num_rows : 0; ?>
                </span>
            </button>

            <div id="notificationDropdown" class="notification-dropdown">
                <h6>Notifications</h6>
                <ul>
                <?php if ($notificationsResult->num_rows > 0): ?>
                    <?php while ($notification = $notificationsResult->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo $notification['message']; ?></strong><br>
                            <small><?php echo date('F j, Y, g:i a', strtotime($notification['created_at'])); ?></small>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="text-muted text-center">No new notifications</li>
                <?php endif; ?>

                </ul>
            </div>
        </div>
    </div>

    <div class="content">
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Date and Status Filter Form -->
        <form method="POST" class="mb-3">
            <div class="form-row">
                <div class="col">
                    <input type="date" name="order_date" class="form-control" value="<?php echo isset($orderDate) ? $orderDate : ''; ?>" required>
                </div>
                <div class="col">
                    <select name="order_status" class="form-control">
                        <option value="">Select Status</option>
                        <option value="Checked Out" <?php echo ($orderStatus === 'Checked Out') ? 'selected' : ''; ?>>Checked Out</option>
                        <option value="Pending" <?php echo ($orderStatus === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Delivering" <?php echo ($orderStatus === 'Delivering') ? 'selected' : ''; ?>>Delivering</option>
                        <option value="Complete" <?php echo ($orderStatus === 'Complete') ? 'selected' : ''; ?>>Complete</option>
                        <option value="Cancelled" <?php echo ($orderStatus === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col">
                    <button type="submit" name="filter" class="btn btn-primary btn-sm">Filter</button>
                </div>
            </div>
        </form>



        <table class="table mt-3">
            <thead>
            <tr>
                <th>Room Number</th>
                <th>Student Number</th>
                <th>Receiver Name</th>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Student Course</th>
                <th>Student Section</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Product Quantity</th>
                <th>Transaction Date</th>
                <th>Email</th>
                <th>Order Status</th>
                <th>Status Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($resultOrders->num_rows > 0) {
                while ($row = $resultOrders->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["room_number"] . "</td>";
                    echo "<td>" . $row["student_number"] . "</td>";
                    echo "<td>" . $row["receiver_name"] . "</td>";
                    echo "<td>" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["first_name"] . " " . $row["last_name"] . "</td>";
                    echo "<td>" . $row["course"] . "</td>";
                    echo "<td>" . $row["section"] . "</td>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>₱" . $row["price"] . "</td>";
                    echo "<td>" . $row["quantity"] . "</td>";
                    echo "<td>" . date("Y-m-d", strtotime($row["order_date"])) . "</td>";
                    echo "<td>" . $row["user_email"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action=''>";
                    echo "<input type='hidden' name='order_id' value='" . $row['order_id'] . "'>";
                    echo "<select name='status' class='form-control'>";
                    echo "<option value='Checked Out'" . ($row['status'] == 'Checked Out' ? ' selected' : '') . ">Checked Out</option>";
                    echo "<option value='Pending'" . ($row['status'] == 'Pending' ? ' selected' : '') . ">Pending</option>";
                    echo "<option value='Delivering'" . ($row['status'] == 'Delivering' ? ' selected' : '') . ">Delivering</option>";
                    echo "<option value='Complete'" . ($row['status'] == 'Complete' ? ' selected' : '') . ">Complete</option>";
                    echo "<option value='Cancelled'" . ($row['status'] == 'Cancelled' ? ' selected' : '') . ">Cancelled</option>";
                    echo "</select>";
                    echo "<button type='submit' class='btn btn-primary btn-sm'>Update</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='12'>No orders found for the selected date or status.</td></tr>";
            }
            ?>
            </tbody>
        </table>
        <!-- Pagination -->
        <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

    </div>
</div>

<script>
    // Toggle notification dropdown visibility
    document.getElementById('notificationBtn').addEventListener('click', function() {
        var dropdown = document.getElementById('notificationDropdown');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    });
</script>

</body>
</html>