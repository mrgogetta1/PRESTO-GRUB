<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in or is not a seller or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: login.php");
    exit;
}

// Fetch completed orders for the logged-in seller
$user_id = $_SESSION['user_id'];

// Initialize date variable
$orderDate = '';

// Check if the form for date filtering has been submitted
if (isset($_POST['filter_date'])) {
    $orderDate = $_POST['order_date'];
}

// Fetch completed orders from the order_complete table
$queryOrders = "SELECT oc.*, u.first_name, u.last_name, u.course, u.section, 
                u.email AS user_email, u.contact_no, p.name AS product_name, 
                p.price, s.store_name 
                FROM order_complete oc
                INNER JOIN users u ON oc.user_id = u.id
                INNER JOIN products p ON oc.product_id = p.product_id
                INNER JOIN stores s ON p.store_id = s.store_id
                WHERE (p.user_id = ? OR p.user_id IN 
                (SELECT store_id FROM stores WHERE user_id = ?))
                AND oc.status = 'Complete'"; // Only fetch completed orders

if (!empty($orderDate)) {
    $queryOrders .= " AND DATE(oc.order_date) = ?";
}

$stmt = $conn->prepare($queryOrders);

if (!empty($orderDate)) {
    $stmt->bind_param("iss", $user_id, $user_id, $orderDate);
} else {
    $stmt->bind_param("ii", $user_id, $user_id); // Fetch orders related to the seller's products
}

$stmt->execute();
$resultOrders = $stmt->get_result();

// Check if the query executed successfully
if (!$resultOrders) {
    echo "Error fetching orders: " . $conn->error;
    exit;
}

// Check if there is a success message to display
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']); // Clear the message after displaying it
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Seller Panel - Completed Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #343a40;
            color: #fff;
            padding: 15px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 1.2em;
        }
        .menu {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-around;
            padding: 5px;
            font-size: 0.9em;
        }
        .content {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .table th, .table td {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Presto Grub Seller Panel - Completed Orders</h1>
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
    </div>
    <div class="content">
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Date Filter Form -->
        <form method="POST" class="mb-3">
            <div class="form-row">
                <div class="col">
                    <input type="date" name="order_date" class="form-control" value="<?php echo $orderDate; ?>" required>
                </div>
                <div class="col">
                    <button type="submit" name="filter_date" class="btn btn-primary btn-sm">Filter</button>
                </div>
            </div>
        </form>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Order Complete ID</th>
                    <th>Order ID</th>
                    <th>User Name</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Order Date</th>
                    <th>User Email</th>
                    <th>Contact No</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($resultOrders->num_rows > 0) {
                while ($row = $resultOrders->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["order_complete_id"] . "</td>";
                    echo "<td>" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["first_name"] . " " . $row["last_name"] . "</td>";
                    echo "<td>" . $row["course"] . "</td>";
                    echo "<td>" . $row["section"] . "</td>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>₱" . $row["price"] . "</td>";
                    echo "<td>" . $row["quantity"] . "</td>";
                    echo "<td>" . $row["order_date"] . "</td>";
                    echo "<td>" . $row["user_email"] . "</td>";
                    echo "<td>" . $row["contact_no"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='12' class='text-center'>No completed orders found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
