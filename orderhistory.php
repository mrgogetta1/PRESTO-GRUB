<?php
session_start();
require_once 'connection/connection.php';  // Ensure this is the correct path to your connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get filter input (e.g., past orders, previous orders)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Prepare the SQL query based on the filter
$sql = "SELECT * FROM orders WHERE user_id = ?";
if ($filter == 'past') {
    $sql .= " AND order_date >= CURDATE()";  // Orders from today or in the future
} elseif ($filter == 'previous') {
    $sql .= " AND order_date < CURDATE()";  // Orders before today
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Presto Grub - Order History</title>
    <link rel="stylesheet" type="text/css" href="css/orderhistory.css">
</head>
<style>
    body {
    background-image: url('uploads/yup.jpg'); /* Add your image path */
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    text-align: center; /* Center the content in body */
    overflow: 
}

.container {
    width: 85%; /* Adjust width to control the container size */
    margin: 0 auto; /* Automatically centers the container */
}

form {
    margin: 20px 0;
    text-align: center; /* Center the form content */
    color: white; /* Set font color to white */
    font-weight: bold; /* Make font bold */
    font-size: 16px; /* Set font size */
}
select, button {
    padding: 8px;
    margin-right: 10px;
}
table {
    width: 80%;
    border-collapse: collapse;
    margin: 20px auto; /* Center the table */
    text-align: center; /* Center align the table content */
}
th, td {
    padding: 12px;
    border: 1px solid #ddd;
}

th {
    background-color: lightgreen;
}

</style>
<body>
    <!-- Include your navbar here -->
    <?php include 'navbar.php'; ?>

    <div class="container"> <!-- Centering container -->
        <!-- Filter Form -->
        <form method="get" action="">
            <label for="filter">Filter orders: </label>
            <select name="filter" id="filter">
                <option value="all" <?php echo ($filter == 'all') ? 'selected' : ''; ?>>All</option>
                <option value="past" <?php echo ($filter == 'past') ? 'selected' : ''; ?>>Past Orders</option>
                <option value="previous" <?php echo ($filter == 'previous') ? 'selected' : ''; ?>>Previous Orders</option>
            </select>
            <button type="submit">Apply Filter</button>
        </form>

        <!-- Order History Table -->
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Product ID</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php
                // Display the fetched orders
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['order_id'] . "</td>";
                        echo "<td>" . $row['order_date'] . "</td>";
                        echo "<td>" . $row['product_id'] . "</td>";
                        echo "<td>" . $row['quantity'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . $row['total_price'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No orders found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
