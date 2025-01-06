<?php
session_start();
require_once '../connection/connection.php';

// Set the default timezone to Asia/Manila (UTC +8)
date_default_timezone_set('Asia/Manila');

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 2) {
    header("Location: login.php");
    exit;
}

// Get the selected date and status from the form or use today's date and 'All' for status
$selectedDate = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');
$selectedStatus = isset($_POST['selected_status']) ? $_POST['selected_status'] : 'All';

// Debug: Show the selected date and status for troubleshooting
// echo "Selected Date: " . $selectedDate . "<br>";
// echo "Selected Status: " . $selectedStatus . "<br>";

// Build the base query
$queryOrders = "SELECT orders.*, users.first_name, users.last_name, users.course, users.section, 
                users.email AS user_email, users.contact_no, products.name AS product_name, 
                products.price, stores.store_name 
                FROM orders
                INNER JOIN users ON orders.user_id = users.id
                INNER JOIN products ON orders.product_id = products.product_id
                INNER JOIN stores ON products.store_id = stores.store_id
                WHERE DATE(orders.order_date) = ?";

// Add a condition for the status if it's not 'All'
if ($selectedStatus !== 'All') {
    $queryOrders .= " AND orders.status = ?";
}

$stmt = $conn->prepare($queryOrders);

// Debug: Check if the query is prepared properly
// echo "SQL Query: " . $queryOrders . "<br>";

if ($selectedStatus !== 'All') {
    // Bind parameters for both date and status
    $stmt->bind_param("ss", $selectedDate, $selectedStatus);
} else {
    // Bind only the date when the status is 'All'
    $stmt->bind_param("s", $selectedDate);
}

$stmt->execute();
$resultOrders = $stmt->get_result();

// Check if the query executed successfully
if (!$resultOrders) {
    echo "Error fetching orders: " . $conn->error;
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Admin Panel - Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<style>
    .container {
        max-width: 1200px;
        margin: auto;
        padding: 20px;
    }

    .header {
        background-color: #343a40;
        color: #fff;
        padding: 20px;
        text-align: center;
    }

    .menu {
        background-color: #f8f9fa;
        border-radius: 5px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        display: flex;
        justify-content: space-around;
        padding: 10px;
    }

    .menu a {
        color: #333;
        text-decoration: none;
        padding: 10px 20px;
        transition: background-color 0.3s ease;
    }

    .menu a:hover {
        background-color: #e9ecef;
        border-radius: 5px;
    }

    .content {
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th, td {
        border: 1px solid #dee2e6;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #343a40;
        color: #fff;
    }

    form {
        margin-bottom: 20px;
    }
</style>

<div class="container">
    <div class="header">
        <h1>Presto Grub Admin Panel - Orders</h1>
        <!-- Logout button -->
        <form method="POST" action="../function/logout.php">
            <button type="submit" class="btn btn-primary">Logout</button>
        </form>
    </div>

    <div class="menu">
        <a href="admin_panel.php" onclick="showDashboard()">Dashboard</a>
        <a href="admin_store.php" onclick="showStores()">Stores</a>
        <a href="admin_product.php" onclick="showProducts()">Products</a>
        <a href="order_admin.php" onclick="showOrders()">Orders</a>
        <a href="admin_order_complete.php" onclick="showCompleteOrders()">Complete Orders</a>
        <a href="admin_category.php" onclick="showUsers()">Categories</a>
        <a href="users.php" onclick="showUsers()">Users</a>
    </div>

    <div class="container">
        <h2>View Orders</h2>
        
        <form method="POST" action="">
            <label for="selected_date">Select Date:</label>
            <input type="date" name="selected_date" value="<?php echo $selectedDate; ?>" class="form-control">

            <label for="selected_status" class="mt-2">Select Status:</label>
            <select name="selected_status" class="form-control">
                <option value="All" <?php echo ($selectedStatus === 'All' ? 'selected' : ''); ?>>All</option>
                <option value="Checked Out" <?php echo ($selectedStatus === 'Checked Out' ? 'selected' : ''); ?>>Checked Out</option>
                <option value="Pending" <?php echo ($selectedStatus === 'Pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="Delivering" <?php echo ($selectedStatus === 'Delivering' ? 'selected' : ''); ?>>Delivering</option>
                <option value="Cancelled" <?php echo ($selectedStatus === 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                <option value="Complete" <?php echo ($selectedStatus === 'Complete' ? 'selected' : ''); ?>>Complete</option>
            </select>

            <button type="submit" class="btn btn-primary mt-2">Show Orders</button>
        </form>

        <table class="table mt-3">
            <thead>
                <tr>
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
                    echo "<td>" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["first_name"] . " " . $row["last_name"] . "</td>";
                    echo "<td>" . $row["course"] . "</td>";
                    echo "<td>" . $row["section"] . "</td>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>" . $row["price"] . "</td>";
                    echo "<td>" . $row["quantity"] . "</td>";
                    echo "<td>" . $row["order_date"] . "</td>";
                    echo "<td>" . $row["user_email"] . "</td>";
                    echo "<td>" . $row["contact_no"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='11'>No orders found for the selected date and status</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>