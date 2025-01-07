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
    <!-- Font Awesome CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">\
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


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
        .card-title i {
    font-size: 2.2rem; /* Adjust icon size to make it larger */
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
        <!-- Total Stores -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Total Stores
                        <i class="fas fa-store"></i>
                    </h5>
                    <p class="card-text"><?php echo $storeCount; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Total Products
                        <i class="fas fa-box"></i>
                    </h5>
                    <p class="card-text"><?php echo $productCount; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Sold Items -->
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Total Sold Items
                        <i class="fas fa-cart-arrow-down"></i>
                    </h5>
                    <p class="card-text"><?php echo $totalOrders; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Income -->
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Total Income
                        <i class="fas fa-money-bill-wave"></i>
                    </h5>
                    <p class="card-text">₱<?php echo number_format($totalIncome, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Income -->
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Today's Income
                        <i class="fas fa-calendar-day"></i>
                    </h5>
                    <p class="card-text">₱<?php echo number_format($todayIncome, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Weekly Income -->
        <div class="col-md-3">
            <div class="card text-white bg-secondary mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Weekly Income
                        <i class="fas fa-calendar-week"></i>
                    </h5>
                    <p class="card-text">₱<?php echo number_format($weeklyIncome, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Monthly Income -->
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Monthly Income
                        <i class="fas fa-calendar-alt"></i>
                    </h5>
                    <p class="card-text">₱<?php echo number_format($monthlyIncome, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Today's Orders -->
        <div class="col-md-3">
            <div class="card text-white bg-light text-dark mb-3">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between">
                        Today's Orders
                        <i class="fas fa-clipboard-list"></i>
                    </h5>
                    <p class="card-text"><?php echo $todayOrders; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Bar Chart for Total Stores, Products, Orders, and Income -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Business Overview</h5>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

<!-- Meter Chart for Today's Income -->
<div class="col-md-6">
    <div class="card mb-3">
        <div class="card-body text-center">
            <h5 class="card-title">Today's Income Meter</h5>
            <div style="position: relative; display: inline-block;">
                <canvas id="meterChart" style="max-width: 100%; max-height: 240px;"></canvas>
                <!-- Arrow and Label -->
                <div style="position: absolute; top: 20%; left: 55%; transform: translate(-50%, -50%); text-align: center;">
                    <span id="achievedLabel" style="font-weight: bold; font-size: 1.2rem; color: #FFA726;"></span>
                    <div style="margin-top: -25px; margin-right: -130px;">
                        <i class="fas fa-arrow-down" style="color: #FFA726;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

    </div>
</div>

 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-gauge"></script>
<script>

window.onload = function () {
    // Bar Chart for Total Stores, Products, Orders, and Income
    const ctxBar = document.getElementById('barChart').getContext('2d');
    const barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Total Stores', 'Total Products', 'Total Sold Items', 'Total Income'],
            datasets: [{
                label: 'Business Overview',
                data: [
                    <?php echo $storeCount; ?>, 
                    <?php echo $productCount; ?>, 
                    <?php echo $totalOrders; ?>, 
                    <?php echo $totalIncome; ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)', 
                    'rgba(75, 192, 192, 0.6)', 
                    'rgba(255, 159, 64, 0.6)', 
                    'rgba(153, 102, 255, 0.6)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)', 
                    'rgba(75, 192, 192, 1)', 
                    'rgba(255, 159, 64, 1)', 
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxMeter = document.getElementById('meterChart').getContext('2d');
const todayIncome = <?php echo $todayIncome; ?>;
const meterChart = new Chart(ctxMeter, {
    type: 'doughnut',
    data: {
        labels: ['Achieved', 'Remaining'],
        datasets: [{
            data: [todayIncome, Math.max(100 - todayIncome, 0)],
            backgroundColor: ['#FFA726', '#E0E0E0'], // Orange and light grey
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        circumference: 180, // Semi-circle
        rotation: 270, // Start from the top
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        }
    }
});

// Add the achieved value dynamically as a label
document.getElementById('achievedLabel').textContent = `₱${todayIncome.toFixed(2)}`;


};


    function showDashboard() {
        document.getElementById("dashboard").style.display = "block";
    }
    // Add other functions for displaying different sections here

    
</script>

</body>
</html>
