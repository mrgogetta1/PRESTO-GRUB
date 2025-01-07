<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 2) {
    header("Location: ./login.php");
    exit;
}

// Fetch data for dashboard metrics
$userCountResult = $conn->query("SELECT COUNT(*) AS total_users FROM users");

// Updated query to count only non-admin active users
$activeUserCountResult = $conn->query("SELECT COUNT(*) AS active_users FROM users WHERE is_active = 1 AND isAdmin != 2");

$storeCountResult = $conn->query("SELECT COUNT(*) AS total_stores FROM stores");
$productCountResult = $conn->query("SELECT COUNT(*) AS total_products FROM products");

// Updated query to calculate total completed orders
$totalSoldItemsResult = $conn->query("SELECT SUM(quantity) AS total_sold FROM orders WHERE status = 'Complete'");

// Fetch data from the result sets
$userCount = $userCountResult->fetch_assoc()['total_users'];
$activeUserCount = $activeUserCountResult->fetch_assoc()['active_users'];
$storeCount = $storeCountResult->fetch_assoc()['total_stores'];
$productCount = $productCountResult->fetch_assoc()['total_products'];
$totalSoldItems = $totalSoldItemsResult ? $totalSoldItemsResult->fetch_assoc()['total_sold'] : 0;

// Fetch the count of completed orders
$completedOrdersResult = $conn->query("SELECT COUNT(*) AS total_complete_orders FROM orders WHERE status = 'Complete'");
$totalCompletedOrders = $completedOrdersResult->fetch_assoc()['total_complete_orders'];

// Fetch the count of canceled orders
$canceledOrdersResult = $conn->query("SELECT COUNT(*) AS total_canceled_orders FROM orders WHERE status = 'Cancelled'");
$totalCanceledOrders = $canceledOrdersResult->fetch_assoc()['total_canceled_orders'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Admin Panel</title>
    <!-- Bootstrap CSS -->
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

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
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
            color: #333;
            text-decoration: none;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }

        .menu a:hover {
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .content {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .content h2 {
            margin-top: 0;
        }

        .chart-container {
            width: 100%;
            height: 350px; /* Ensuring all charts are the same height */
        }
    </style>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Presto Grub Admin Panel</h1>

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
    <div class="content" id="dashboard">
            <h2 class="text-center">Welcome to the Presto Grub Admin Panel!</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                        <h5 class="card-title"><a href="users.php" class="text-white">Total Users</a></h5>
                            <p class="card-text"><?php echo $userCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><a href="users.php" class="text-white">Active Users</a></h5></h5>
                            <p class="card-text"><?php echo $activeUserCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><a href="admin_store.php" class="text-white">Total Stores</a></h5></h5>
                            <p class="card-text"><?php echo $storeCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><a href="admin_product.php" class="text-white">Total Products</a></h5></h5>
                            <p class="card-text"><?php echo $productCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><a href="admin_order_complete.php" class="text-white">Completed Orders</a></h5></h5>
                            <p class="card-text"><?php echo $totalCompletedOrders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Canceled Orders</h5>
                            <p class="card-text"><?php echo $totalCanceledOrders; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <div class="row">
            <div class="col-md-6 chart-container">
                <canvas id="userChart"></canvas>
            </div>
            <div class="col-md-6 chart-container">
                <canvas id="storeProductChart"></canvas>
            </div>
            <div class="col-md-6 chart-container">
                <canvas id="completedVsCanceledChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Chart.js Charts -->
<script>
    // Data for User Chart
    const userData = {
        labels: ['Total Users', 'Active Users'],
        datasets: [{
            label: 'User Statistics',
            data: [<?php echo $userCount; ?>, <?php echo $activeUserCount; ?>],
            backgroundColor: ['#007bff', '#28a745'],
            borderWidth: 1
        }]
    };

    const userConfig = {
        type: 'pie',
        data: userData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'User Statistics'
                }
            }
        }
    };

    // Store, Product, and Sales Chart
    const storeProductData = {
        labels: ['Stores', 'Products'],
        datasets: [{
            label: 'Store and Product Statistics',
            data: [<?php echo $storeCount; ?>, <?php echo $productCount; ?>],
            backgroundColor: ['#ffc107', '#dc3545'],
            borderWidth: 1
        }]
    };

    const storeProductConfig = {
        type: 'bar',
        data: storeProductData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Store and Product Statistics'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    // Completed vs Canceled Orders Chart
    const completedVsCanceledData = {
        labels: ['Completed Orders', 'Canceled Orders'],
        datasets: [{
            label: 'Orders',
            data: [<?php echo $totalCompletedOrders; ?>, <?php echo $totalCanceledOrders; ?>],
            backgroundColor: ['#dc3545', '#6c757d'], // Red for Completed, Gray for Canceled
            borderWidth: 1
        }]
    };

    const completedVsCanceledConfig = {
        type: 'bar',
        data: completedVsCanceledData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Completed vs Canceled Orders'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    // Create all the charts
    const userChart = new Chart(document.getElementById('userChart'), userConfig);
    const storeProductChart = new Chart(document.getElementById('storeProductChart'), storeProductConfig);
    const completedVsCanceledChart = new Chart(document.getElementById('completedVsCanceledChart'), completedVsCanceledConfig);
</script>

</body>
</html>
