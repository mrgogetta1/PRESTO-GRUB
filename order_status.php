<?php
session_start();
require_once 'connection/connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? ''; // Fetch username or set as empty string
    $profile_picture = $_SESSION['profile_picture'] ?? 'default-profile.png'; // Use default image if not set
} else {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch user information
$query = "SELECT isAdmin, profile_picture, username FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $_SESSION['isAdmin'] = $user['isAdmin'];
    $_SESSION['profile_picture'] = $user['profile_picture'];
    $_SESSION['username'] = $user['username'];
} else {
    echo "User not found.";
    exit();
}

$user_id = $_SESSION['user_id'];
$search_query = $_GET['search'] ?? '';

// Pagination settings
$items_per_page = 3;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Fetch total number of matching orders
$count_query = "
    SELECT COUNT(*) AS total 
    FROM orders
    JOIN products ON products.product_id = orders.product_id
    JOIN product_variants ON product_variants.variant_id = orders.variant_id
    WHERE orders.user_id = ? 
    AND (products.name LIKE ? OR orders.status LIKE ?)
    AND orders.status IN ('Checked Out', 'Pending', 'Delivering')";
$count_stmt = $conn->prepare($count_query);
$like_search = '%' . $search_query . '%';
$count_stmt->bind_param("iss", $user_id, $like_search, $like_search);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_orders = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $items_per_page);

// Fetch paginated and filtered orders
$query = "
    SELECT products.*, product_variants.variant_name, product_variants.sku, orders.quantity, 
           orders.payment_method, orders.order_id, orders.status, orders.order_date
    FROM orders
    JOIN products ON products.product_id = orders.product_id
    LEFT JOIN product_variants ON product_variants.variant_id = orders.variant_id
    WHERE orders.user_id = ? 
    AND (products.name LIKE ? OR orders.status LIKE ?)
    AND orders.status IN ('Checked Out', 'Pending', 'Delivering')
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("issii", $user_id, $like_search, $like_search, $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    echo "Order ID: " . $order_id; // Debugging
    
    $cancel_query = "UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND user_id = ?";
    $cancel_stmt = $conn->prepare($cancel_query);
    $cancel_stmt->bind_param("ii", $order_id, $user_id);
    
    if ($cancel_stmt->execute()) {
        echo "Order successfully cancelled."; // Debugging
        // Redirect to order status page after successful cancellation
        header("Location: order_status.php");
        exit();
    } else {
        echo "Failed to cancel the order. Please try again.";
    }
    $cancel_stmt->close();
}

// Fetch notifications for the logged-in user
$notificationQuery = $conn->prepare(
    "SELECT id, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC"
);
$notificationQuery->bind_param("i", $user_id);
$notificationQuery->execute();
$notifications = $notificationQuery->get_result();

// Fetch the unread count
$unreadCountQuery = $conn->prepare(
    "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0"
);
$unreadCountQuery->bind_param("i", $user_id);
$unreadCountQuery->execute();
$unreadCountQuery->bind_result($unreadCount);
$unreadCountQuery->fetch();
$unreadCountQuery->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub - Order</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="uploads/chef-hat.png" type="image/svg+xml">
    <link rel="stylesheet" type="text/css" href="css/order_status.css">
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 2000;
            top: 400px;
            right: 80px;
            margin-bottom: 100px !important;
        }
        .pagination a {
            text-decoration: none;
            color: #444;
            padding: 10px 15px;
            border: 1px solid #ddd;
            margin: 0 5px;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination a:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <div class="header-center">
            <form action="order_status.php" method="GET">
                <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="cart-profile-container">
            <div class="notification-icon">
                <i class="fa fa-bell"></i>  
            </div>
            <div class="cart">
                <i class="fas fa-shopping-cart cart-icon"></i>
            </div>

            <!-- Profile Dropdown -->
            <?php if (isset($_SESSION['username'])): ?>
    <div class="profile">
        <div class="profile-details">
            <?php 
            // Check if $profile_picture is set and is not empty
            if (!empty($profile_picture)) {
                $profilePicPath = "uploads/" . htmlspecialchars($profile_picture);
            } else {
                $profilePicPath = "uploads/default-profile.jpg"; // A fallback image if no profile picture is set
            }
            ?>
            <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" class="profile-pic">
            <span class="profile-name"><?php echo htmlspecialchars($username); ?></span>
            <i class="fas fa-chevron-down profile-dropdown-icon"></i>
        </div>
        <div class="dropdown">
            <!-- <a href="profile.php"><i class="fas fa-user"></i> My Profile</a> -->
            <a href="account.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="function/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
<?php else: ?>
    <!-- Login Button -->
    <div class="login">
        <a href="login.php" class="btn-login">Login</a>
    </div>
<?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="cart">
            <div class="orders-wrapper">
                <div class="products">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="product-row">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="product">
                                    <img src="productimg/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
                                    <div class="product-info">
                                        <h1 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h1>
                                        <p class="status">Status: <span class="status-text"><?php echo htmlspecialchars($row['status']); ?></span></p>
                                        <p class="quantity">Quantity: <?php echo htmlspecialchars($row['quantity']); ?></p>
                                        <p class="order-date">Order Date: <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($row['order_date']))); ?></p>
                                        <p class="order-id">Order ID: <?php echo htmlspecialchars($row['order_id']); ?></p>

                                        <?php if ($row['status'] !== 'Complete' && $row['status'] !== 'Delivering'): ?>
                                            <form method="POST" class="cancel-form">
                                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                <button type="submit" name="cancel_order" class="cancel-button" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel Order</button>
                                            </form>
                                        <?php endif; ?>
                                    </div> <!-- End product-info -->
                                </div> <!-- End product -->
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-order">No orders found matching your search.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
        <!-- Pagination -->
        <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>" 
               class="<?php echo ($i === $current_page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Next</a>
        <?php endif; ?>
    </div>
</div>


<?php include 'footer_chat.php'; ?>


    <!-- Footer -->



<script>


document.addEventListener('DOMContentLoaded', function () {
  const toggleBtn = document.getElementById('toggle-btn');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.querySelector('.main-content');

  toggleBtn.addEventListener('click', function () {
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('shifted');
  });
});
     


// Toggle the notification dropdown visibility when the notification icon is clicked
document.querySelector('.notification-icon').addEventListener('click', function() {
    const dropdown = document.querySelector('.notification-dropdown');
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';

    // Mark notifications as read by calling the mark_notifications_read.php file
    fetch('seller/mark_notifications_read.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If successful, mark notifications as read on the page
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                });

                // Optionally, update the notification count here
                document.querySelector('.notification-count').style.display = 'none';  // Hide the count after it's read
            }
        });
});

</script>

</body>
</html>
