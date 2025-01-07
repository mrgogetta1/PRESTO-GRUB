<?php
session_start();
// Include TCPDF
require_once '../vendor/autoload.php';
require_once '../connection/connection.php';

// Set the default timezone to Asia/Manila (UTC +8)
date_default_timezone_set('Asia/Manila');

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 2) {
    header("Location: login.php");
    exit;
}

$selectedDate = date('Y-m-d'); // Default to today's date if not selected

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['selected_date'])) {
        $selectedDate = $_POST['selected_date']; // Get the selected date
    }

    // Handling status update
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];

        // Update the order status in orders table
        $updateStatusStmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $updateStatusStmt->bind_param("si", $status, $order_id);
        if ($updateStatusStmt->execute()) {
            // Move the order to the completed orders table if status is 'Complete'
            if ($status == 'Complete') {
                $moveToCompleteStmt = $conn->prepare("INSERT INTO order_complete (order_id, user_id, order_date, status) 
                                                      SELECT order_id, user_id, order_date, 'Complete' 
                                                      FROM orders 
                                                      WHERE order_id = ?");
                $moveToCompleteStmt->bind_param("i", $order_id);
                if ($moveToCompleteStmt->execute()) {
                    // Delete the order from the orders table
                    $deleteFromOrdersStmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
                    $deleteFromOrdersStmt->bind_param("i", $order_id);
                    if ($deleteFromOrdersStmt->execute()) {
                        header("Location: admin_order_complete.php");
                        exit;
                    }
                }
            } else {
                header("Location: admin_order_pending.php");
                exit;
            }
        }
        $updateStatusStmt->close();
    }
}

// Query to fetch all orders with "Complete" status
$queryOrders = "SELECT orders.*, users.first_name, users.last_name, users.course, users.section, users.email AS user_email, users.contact_no 
                FROM orders 
                INNER JOIN users ON orders.user_id = users.id
                WHERE orders.status = 'Complete' AND DATE(orders.order_date) = ?";

$stmt = $conn->prepare($queryOrders);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$resultOrders = $stmt->get_result();

// Check if the query executed successfully
if (!$resultOrders) {
    echo "Error fetching completed orders: " . $conn->error;
    exit;
}

// Handle PDF generation
if (isset($_POST['generate_pdf'])) {
    // Create new PDF document
    $pdf = new TCPDF();
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Completed Orders Report - ' . $selectedDate, 0, 1, 'C');

    // Set font for table content
    $pdf->SetFont('Helvetica', '', 12);
    
     // Add Store Header
     $pdf->Cell(0, 10, 'Store Name: Presto Grub', 0, 1, 'L'); // Change this to the actual store name
    
     // Add the Date and Time the report is generated
     $pdf->Cell(0, 10, 'Report Generated: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
     
     // Add a summary of the report
     $pdf->Cell(0, 10, 'This report contains completed orders for ' . $selectedDate, 0, 1, 'L');
     
     // Add a horizontal line
     $pdf->Ln(5);
     $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
 
     // Set font for table content
     $pdf->SetFont('Helvetica', '', 12);
     
     // Add table header
     $pdf->Ln(10);
     $pdf->Cell(25, 10, 'Order ID', 1, 0, 'C');
     $pdf->Cell(40, 10, 'User Name', 1, 0, 'C');
     $pdf->Cell(30, 10, 'Course', 1, 0, 'C');
     $pdf->Cell(20, 10, 'Section', 1, 0, 'C');
     $pdf->Cell(35, 10, 'Order Date', 1, 0, 'C');
     $pdf->Cell(50, 10, 'User Email', 1, 0, 'C');
     $pdf->Cell(30, 10, 'Contact No', 1, 1, 'C');
 
     // Loop through orders and add them to the table
     while ($row = $resultOrders->fetch_assoc()) {
         $pdf->Cell(25, 10, $row["order_id"], 1, 0, 'C');
         $pdf->Cell(40, 10, $row["first_name"] . ' ' . $row["last_name"], 1, 0, 'C');
         $pdf->Cell(30, 10, $row["course"], 1, 0, 'C');
         $pdf->Cell(20, 10, $row["section"], 1, 0, 'C');
         $pdf->Cell(35, 10, $row["order_date"], 1, 0, 'C');
         $pdf->Cell(50, 10, $row["user_email"], 1, 0, 'C');
         $pdf->Cell(30, 10, $row["contact_no"], 1, 1, 'C');
     }
 
     // Add total number of orders at the end of the report
     $totalOrders = $resultOrders->num_rows;
     $pdf->Ln(10);
     $pdf->Cell(0, 10, 'Total Completed Orders: ' . $totalOrders, 0, 1, 'C');
 
     // Output the PDF as a downloadable file
     $pdf->Output('completed_orders_report_' . $selectedDate . '.pdf', 'D'); // 'D' will force the PDF to download
 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Admin Panel - Complete Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
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
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Presto Grub Admin Panel - Complete Orders</h1>
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
        <h2>View Complete Orders</h2>

        <form method="POST" action="">
            <label for="selected_date">Select Date:</label>
            <input type="date" name="selected_date" value="<?php echo $selectedDate; ?>" class="form-control">
            <button type="submit" class="btn btn-primary mt-2">Show Orders</button>
        </form>

        <form method="POST" action="">
            <button type="submit" name="generate_pdf" class="btn btn-success mt-2">Generate PDF Report</button>
        </form>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User Name</th>
                    <th>Course</th>
                    <th>Section</th>
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
                    echo "<td>" . $row["order_date"] . "</td>";
                    echo "<td>" . $row["user_email"] . "</td>";
                    echo "<td>" . $row["contact_no"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>No completed orders found for the selected date.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
