<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in or is not a seller (Admin: 1, Seller: 2)
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: ../login.php");
    exit;
}

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Function to fetch store count for the seller
function fetchStoreCount($conn, $user_id) {
    $query = "SELECT COUNT(*) AS total FROM stores WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

// Function to fetch product count for the seller
function fetchProductCount($conn, $user_id) {
    $query = "SELECT COUNT(*) AS total FROM products WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

// Function to fetch total orders
function fetchTotalOrders($conn, $user_id, $dateCondition = "") {
    $query = "SELECT COUNT(*) AS total_orders FROM orders o JOIN products p ON o.product_id = p.product_id WHERE p.user_id = ? $dateCondition";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total_orders'];
}

// Function to fetch income
function fetchIncome($conn, $user_id, $dateCondition = "") {
    $query = "
        SELECT SUM(p.price * o.quantity) AS income 
        FROM orders o 
        JOIN products p ON o.product_id = p.product_id 
        WHERE o.status = 'Complete' AND p.user_id = ? $dateCondition
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['income'] ?: 0;
}

// Get dashboard metrics
$storeCount = fetchStoreCount($conn, $user_id);
$productCount = fetchProductCount($conn, $user_id);

// Get total orders for various periods
$totalOrders = fetchTotalOrders($conn, $user_id);
$todayOrders = fetchTotalOrders($conn, $user_id, "AND DATE(CONVERT_TZ(o.order_date, '+00:00', '+08:00')) = CURDATE()");
$weeklyOrders = fetchTotalOrders($conn, $user_id, "AND DATE(CONVERT_TZ(o.order_date, '+00:00', '+08:00')) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$monthlyOrders = fetchTotalOrders($conn, $user_id, "AND DATE(CONVERT_TZ(o.order_date, '+00:00', '+08:00')) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");

// Get income for different periods
$totalIncome = fetchIncome($conn, $user_id);
$todayIncome = fetchIncome($conn, $user_id, "AND DATE(CONVERT_TZ(o.order_date, '+00:00', '+08:00')) = CURDATE()");
$weeklyIncome = fetchIncome($conn, $user_id, "AND DATE(CONVERT_TZ(o.order_date, '+00:00', '+08:00')) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$monthlyIncome = fetchIncome($conn, $user_id, "AND DATE(CONVERT_TZ(o.order_date, '+00:00', '+08:00')) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Seller Panel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
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
        .content {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Presto Grub Seller Panel</h1>
        <form method="POST" action="../function/logout.php">
            <button type="submit" class="btn btn-primary">Logout</button>
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
    <div class="content" id="dashboard">
        <h2 class="text-center">Dashboard</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Stores</h5>
                        <p class="card-text"><?php echo $storeCount; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <p class="card-text"><?php echo $productCount; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Sold Items</h5>
                        <p class="card-text"><?php echo $totalOrders; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Income</h5>
                        <p class="card-text">₱<?php echo number_format($totalIncome, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Today's Income</h5>
                        <p class="card-text">₱<?php echo number_format($todayIncome, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-secondary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Weekly Income</h5>
                        <p class="card-text">₱<?php echo number_format($weeklyIncome, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Income</h5>
                        <p class="card-text">₱<?php echo number_format($monthlyIncome, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-light text-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Today's Orders</h5>
                        <p class="card-text"><?php echo $todayOrders; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Weekly Orders</h5>
                        <p class="card-text"><?php echo $weeklyOrders; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Orders</h5>
                        <p class="card-text"><?php echo $monthlyOrders; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function showDashboard() {
        document.getElementById("dashboard").style.display = "block";
    }
    // Add other functions for displaying different sections here
</script>

</body>
</html>
