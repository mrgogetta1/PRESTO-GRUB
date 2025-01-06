<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is logged in and is a seller or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Set default date range to the current month
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t');

// Download CSV for customer report
if (isset($_POST['download_customer_csv'])) {
    $csvData = [];
    
    // Add a decorative header to the CSV
    $csvData[] = ['*** Customer Report ***']; // Decorative title
    $csvData[] = ['Generated on:', date('Y-m-d H:i:s')]; // Date and time
    $csvData[] = ['']; // Empty row for spacing
    
    // Add the main report header
    $csvHeader = ['First Name', 'Last Name', 'Email', 'Total Orders'];
    $csvData[] = $csvHeader; // Add header row to CSV data
    
    // Fetch customer report data
    $customerQuery = "SELECT u.first_name, u.last_name, u.email, COUNT(oc.order_id) as total_orders
                      FROM orders oc
                      INNER JOIN users u ON oc.user_id = u.id
                      INNER JOIN products p ON oc.product_id = p.product_id
                      WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?
                      GROUP BY u.id";
    $stmt = $conn->prepare($customerQuery);
    $stmt->bind_param("iss", $seller_id, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $csvData[] = [$row['first_name'], $row['last_name'], $row['email'], $row['total_orders']];
    }
    
    // Add a separator row
    $csvData[] = ['']; // Empty row for spacing
    $csvData[] = ['*** Sales Summary ***']; // Section title
    
    // Add sales summary
    $salesQuery = "SELECT COUNT(oc.order_id) as total_orders, SUM(p.price * oc.quantity) as total_revenue 
                   FROM orders oc
                   INNER JOIN products p ON oc.product_id = p.product_id
                   WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($salesQuery);
    $stmt->bind_param("iss", $seller_id, $startDate, $endDate);
    $stmt->execute();
    $salesReport = $stmt->get_result()->fetch_assoc();
    $csvData[] = ['Total Orders', 'Total Revenue'];
    $csvData[] = [$salesReport['total_orders'], $salesReport['total_revenue']];
    
    // Add a decorative footer
    $csvData[] = ['']; // Empty row for spacing
    $csvData[] = ['*** End of Report ***']; // Footer

    // Call the CSV download function
    download_csv('customer_report.csv', $csvData);
}

// Include TCPDF
require_once '../vendor/autoload.php';

// Handle download PDF request
if (isset($_POST['download_customer_pdf'])) {
    // Fetch customer data for the report
    $customerQuery = "SELECT u.first_name, u.last_name, u.email, COUNT(oc.order_id) as total_orders
                      FROM orders oc
                      INNER JOIN users u ON oc.user_id = u.id
                      INNER JOIN products p ON oc.product_id = p.product_id
                      WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?
                      GROUP BY u.id";
    $stmt = $conn->prepare($customerQuery);
    $stmt->bind_param("iss", $seller_id, $startDate, $endDate);
    $stmt->execute();
    $customers = $stmt->get_result();

    // Fetch sales summary
    $salesQuery = "SELECT COUNT(oc.order_id) as total_orders, SUM(p.price * oc.quantity) as total_revenue 
                   FROM orders oc
                   INNER JOIN products p ON oc.product_id = p.product_id
                   WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($salesQuery);
    $stmt->bind_param("iss", $seller_id, $startDate, $endDate);
    $stmt->execute();
    $salesReport = $stmt->get_result()->fetch_assoc();

    // Fetch product performance
    $productPerformanceQuery = "SELECT p.name, SUM(oc.quantity) as units_sold, SUM(p.price * oc.quantity) as revenue 
                                FROM orders oc
                                INNER JOIN products p ON oc.product_id = p.product_id
                                WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?
                                GROUP BY p.product_id 
                                ORDER BY revenue DESC";
    $stmt = $conn->prepare($productPerformanceQuery);
    $stmt->bind_param("iss", $seller_id, $startDate, $endDate);
    $stmt->execute();
    $productPerformance = $stmt->get_result();

    // Fetch inventory report
    $inventoryReportQuery = "SELECT p.name, p.stock_quantity
                             FROM products p
                             WHERE p.user_id = ?";
    $stmt = $conn->prepare($inventoryReportQuery);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $inventoryReport = $stmt->get_result();

    // Create a new PDF document
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Seller Report');
    $pdf->SetHeaderData('', 0, 'Seller Report', 'Generated on: ' . date('Y-m-d H:i:s'));
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setFontSubsetting(true);

    // Add a page
    $pdf->AddPage();

    // Title
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Seller Report', 0, 1, 'C');
    $pdf->Ln(5);

    // Sales Summary
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 10, 'Sales Summary', 0, 1, 'L');
    $pdf->Ln(5);
    $pdf->Cell(0, 10, 'Total Orders: ' . $salesReport['total_orders'], 0, 1);
    $pdf->Cell(0, 10, 'Total Revenue: ₱' . number_format($salesReport['total_revenue'], 2), 0, 1);
    $pdf->Ln(10);

    // Customer Data
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 10, 'Customer Report', 0, 1, 'L');
    $pdf->Ln(5);

    // Customer Table Header
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(50, 10, 'First Name', 1);
    $pdf->Cell(50, 10, 'Last Name', 1);
    $pdf->Cell(50, 10, 'Email', 1);
    $pdf->Cell(30, 10, 'Total Orders', 1);
    $pdf->Ln();

    // Populate customer table
    $pdf->SetFont('dejavusans', '', 10);
    while ($row = $customers->fetch_assoc()) {
        $pdf->Cell(50, 10, $row['first_name'], 1);
        $pdf->Cell(50, 10, $row['last_name'], 1);
        $pdf->Cell(50, 10, $row['email'], 1);
        $pdf->Cell(30, 10, $row['total_orders'], 1);
        $pdf->Ln();
    }

    $pdf->Ln(10);

    // Product Performance
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 10, 'Product Performance', 0, 1, 'L');
    $pdf->Ln(5);

    // Product Performance Table Header
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(70, 10, 'Product Name', 1);
    $pdf->Cell(30, 10, 'Units Sold', 1);
    $pdf->Cell(40, 10, 'Revenue', 1);
    $pdf->Ln();

    // Populate Product Performance Table
    $pdf->SetFont('dejavusans', '', 10);
    while ($row = $productPerformance->fetch_assoc()) {
        $pdf->Cell(70, 10, $row['name'], 1);
        $pdf->Cell(30, 10, $row['units_sold'], 1);
        $pdf->Cell(40, 10, '₱' . number_format($row['revenue'], 2), 1);
        $pdf->Ln();
    }

    $pdf->Ln(10);

    // Inventory Report
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 10, 'Inventory Report', 0, 1, 'L');
    $pdf->Ln(5);

    // Inventory Table Header
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(70, 10, 'Product Name', 1);
    $pdf->Cell(40, 10, 'Stock Status', 1);
    $pdf->Cell(30, 10, 'Total Stocks', 1);
    $pdf->Ln();

    // Populate Inventory Table
    $pdf->SetFont('dejavusans', '', 10);
    while ($row = $inventoryReport->fetch_assoc()) {
        $stockStatus = ($row['stock_quantity'] > 0) ? 'In Stock' : 'Out of Stock';
        $pdf->Cell(70, 10, $row['name'], 1);
        $pdf->Cell(40, 10, $stockStatus, 1);
        $pdf->Cell(30, 10, $row['stock_quantity'], 1);
        $pdf->Ln();
    }

    // Close and output PDF
    $pdf->Output('seller_report.pdf', 'D');
    exit;
}


// Function to download CSV
function download_csv($filename, $data) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}




// Fetching sales reports (with date range)
$salesReportQuery = "SELECT COUNT(oc.order_id) as total_orders, SUM(p.price * oc.quantity) as total_revenue 
                     FROM orders oc
                     INNER JOIN products p ON oc.product_id = p.product_id
                     WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?";
$salesStmt = $conn->prepare($salesReportQuery);
$salesStmt->bind_param("iss", $seller_id, $startDate, $endDate);
$salesStmt->execute();
$salesReport = $salesStmt->get_result()->fetch_assoc();

// Fetching product performance reports (with date range)
$productPerformanceQuery = "SELECT p.name, SUM(oc.quantity) as units_sold, SUM(p.price * oc.quantity) as revenue 
                            FROM orders oc
                            INNER JOIN products p ON oc.product_id = p.product_id
                            WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?
                            GROUP BY p.product_id 
                            ORDER BY revenue DESC";
$productStmt = $conn->prepare($productPerformanceQuery);
$productStmt->bind_param("iss", $seller_id, $startDate, $endDate);
$productStmt->execute();
$productPerformance = $productStmt->get_result();

// Fetching customer reports (with date range)
$customerReportQuery = "SELECT u.first_name, u.last_name, u.email, COUNT(oc.order_id) as total_orders
                        FROM orders oc
                        INNER JOIN users u ON oc.user_id = u.id
                        INNER JOIN products p ON oc.product_id = p.product_id
                        WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?
                        GROUP BY u.id";
$customerStmt = $conn->prepare($customerReportQuery);
$customerStmt->bind_param("iss", $seller_id, $startDate, $endDate);
$customerStmt->execute();
$customerReport = $customerStmt->get_result();

// Fetching inventory reports (with no date range)
$inventoryReportQuery = "SELECT p.name, p.stock_quantity
                         FROM products p
                         WHERE p.user_id = ?";
$inventoryStmt = $conn->prepare($inventoryReportQuery);
$inventoryStmt->bind_param("i", $seller_id);
$inventoryStmt->execute();
$inventoryReport = $inventoryStmt->get_result();

// Fetching financial reports (with date range)
$financialReportQuery = "SELECT SUM(p.price * oc.quantity) as total_revenue, (SUM(p.price * oc.quantity) * 0.1) as expenses, 
                         SUM(p.price * oc.quantity) - (SUM(p.price * oc.quantity) * 0.1) as profit_margin
                         FROM orders oc
                         INNER JOIN products p ON oc.product_id = p.product_id
                         WHERE p.user_id = ? AND oc.status = 'Complete' AND oc.order_date BETWEEN ? AND ?";
$financialStmt = $conn->prepare($financialReportQuery);
$financialStmt->bind_param("iss", $seller_id, $startDate, $endDate);
$financialStmt->execute();
$financialReport = $financialStmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Report Panel</title>
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
        <h1>Seller Report Panel</h1>
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
    <!-- Date Range Filter -->
    <div class="date-filter">
        <form method="POST">
            <div class="form-row">
                <div class="col">
                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col">
                <form method="POST">
                    <button type="submit" name="download_customer_pdf" class="btn btn-info">Download Customer PDF</button>
                </form>
                </div>
            </div>
        </form>
    </div>

    <!-- Sales Report -->
    <div class="content">
        <h3>Sales Report</h3>
        <p>Total Orders: <?php echo $salesReport['total_orders']; ?></p>
        <p>Total Revenue: ₱<?php echo number_format($salesReport['total_revenue'], 2); ?></p>
    </div>

    <!-- Product Performance Report -->
    <div class="content">
        <h3>Product Performance</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Units Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $productPerformance->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['units_sold']; ?></td>
                        <td>₱<?php echo number_format($product['revenue'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Customer Report -->
    <div class="content">
        <h3>Customer Report</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Total Orders</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($customer = $customerReport->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $customer['first_name']; ?></td>
                        <td><?php echo $customer['last_name']; ?></td>
                        <td><?php echo $customer['email']; ?></td>
                        <td><?php echo $customer['total_orders']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Inventory Report -->
    <div class="content">
        <h3>Inventory Report</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Stock Status</th>
                    <th>Total of Stocks</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($inventory = $inventoryReport->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $inventory['name']; ?></td>
                        <td><?php echo ($inventory['stock_quantity'] > 0) ? 'In Stock' : 'Out of Stock'; ?></td>
                        <td><?php echo $inventory['stock_quantity']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Financial Report -->
    <div class="content">
        <h3>Financial Report</h3>
        <p>Total Revenue: ₱<?php echo number_format($financialReport['total_revenue'], 2); ?></p>
        <p>Expenses: ₱<?php echo number_format($financialReport['expenses'], 2); ?></p>
        <p>Profit Margin: ₱<?php echo number_format($financialReport['profit_margin'], 2); ?></p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
