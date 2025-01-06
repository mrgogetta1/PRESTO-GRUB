<?php
session_start();
require_once 'connection/connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? ''; 
    $profile_picture = $_SESSION['profile_picture'] ?? 'default-profile.png'; 
} else {
    header("Location: login.php");
    exit();
}

$store_id = $_GET['store_id'];

// Function to log store views in user_store_history


// Get logged-in user's ID
$user_id = $_SESSION['user_id'];


// Fetch store details
$stmt = $conn->prepare("SELECT * FROM stores WHERE store_id = ?");
$stmt->bind_param("i", $store_id);
$stmt->execute();
$store_result = $stmt->get_result();
$store = $store_result->fetch_assoc();
$stmt->close();

if (!$store) {
    die('Error: Store not found.');
}

// Fetch products for the store
$stmt = $conn->prepare("SELECT * FROM products WHERE store_id = ?");
$stmt->bind_param("i", $store_id);
$stmt->execute();
$product_result = $stmt->get_result();
$stmt->close();

$search_query = isset($_GET['search']) ? $_GET['search'] : '';  // Set search query
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC';

// Pagination setup
$limit = 8;  // Maximum products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// SQL query with search functionality and pagination
$sql = "
    SELECT p.*, 
        IFNULL(AVG(pc.rating), 0) AS avg_rating,
        COUNT(pc.rating) AS total_reviews 
    FROM products p
    LEFT JOIN product_comments pc ON p.product_id = pc.product_id
    WHERE p.name LIKE '%$search_query%'
    GROUP BY p.product_id
    ORDER BY avg_rating $sort_order
    LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Calculate total pages
$total_sql = "SELECT COUNT(*) AS total FROM products WHERE name LIKE '%$search_query%'";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/store.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title><?php echo htmlspecialchars($store['store_name']); ?> - Presto Grub</title>
    <link rel="stylesheet" type="text/css" href="css/Index.css">

    <style>

        /* Ensure no overflow in case of screen resize */
body {
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Prevent horizontal scrolling */
    overflow-y: auto;   /* Enable vertical scrolling */
  }
  
  /* General body and html styles */
  html, body {
    height: 117%;
  }
  h2, h3 {
    font-weight: 600;
    color: black;
    margin-top: 60px;
  }
  
  /* Sidebar Styling */
  .sidebar {
    width: 200px;
    background: linear-gradient(45deg, #2e8b57, #042d86);
    color: white;
    padding: 20px;
    height: 100%;
    position: fixed;
    top: 0;
    left: 0px; /* Initially hidden */
    z-index: 10;
    transition: left 0.4s ease, box-shadow 0.4s ease;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  
    @media (max-width: 400px){
        width: 160px;
    }
  }
  
  /* Sidebar when active */
  .sidebar.active {
    left: 0; /* Slide in from the left */
  }
  
  /* Sidebar Logo */
  .sidebar-logo {
    font-size: 1.8rem;
    font-weight: bold;
    color: #fff;
    text-align: center;
    margin-bottom: 20px;
    margin-left: -20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }
  
  /* Sidebar Menu Items */
  .sidebar-menu {
    list-style: none;
    padding: 0;
    margin-top: -40px;
    position: relative;
    top: -150px;
  
    @media (max-width: 400px){
      margin-top: 200px;
  }
  }
  
  .sidebar-menu li {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    font-size: 1.2rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
  }
  
  .sidebar-menu li i {
    font-size: 1.4rem;
  }
  
  /* Hover and Active State */
  .sidebar-menu li:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateX(5px);
  }
  
  .sidebar-menu li:active {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateX(10px);
  }
  
  /* Sidebar Footer */
  .sidebar-footer {
    text-align: center;
    font-size: 0.9rem;
    position: relative;
    top: -30px;
    color: rgba(255, 255, 255, 0.8);
    border-top: 1px solid rgba(255, 255, 255, 0.2);
  }
  
  /* Smooth Toggle Button */
  .toggle-btn {
    position: relative;
    top: -5px;
    margin-top: 25px;
    margin-left: 50px;
    z-index: 20;
    font-size: 30px;
    cursor: pointer;
    border: none;
    display: none;
    color: white;
    background-color: #1b5e20;
    padding: 10px;
    border-radius: 50%;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
    transition: background-color 0.3s, transform 0.2s;
  }
  
  .toggle-btn:hover {
    background-color: #2e8b57;
    transform: rotate(90deg);
  }
  

  /* Main Content Styles */
.main-content {
    margin-left: 250px; /* No shift when sidebar is toggled */
    padding: 20px;
    margin-top: 20px;
    margin-bottom: 200px;
  
    transition: margin-left 0.4s ease; /* Smooth transition */
  
  }
  
  
  /* When Sidebar is hidden */
  .main-content.fullscreen {
    margin-left: 0; /* Fullscreen mode when sidebar is hidden */
  }
  
  /* When Sidebar is visible */
  .main-content.shifted {
    margin-left: 350px; /* Matches sidebar width */
  
    @media (max-width: 400px){
        margin-left: 200px;
    }
  }
  
  /* Ensure smooth transition for main content */
  .main-content {
    transition: margin-left 0.4s ease;
  }

  .main-border{
    background-color: white;
    width: 100%;
  }
  
  /* Notification Section */
  .notification {
    position: relative;
    display: flex;
    align-items: center;
  }
  
  .notification-icon {
    font-size: 24px;
    cursor: pointer;
    color: #555;
    transition: color 0.3s;
  }
  
  .notification-icon:hover {
    color: #2e8b57;
  }
  
  /* Notification Count */
  .notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 5px;
    font-size: 14px;
  }
  
  .notification-dropdown {
    position: absolute;
    top: 50px;
    right: 0;
    background-color: #fff;
    border: 1px solid #ccc;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 300px;
    display: none;  /* Ensure it's hidden initially */
    max-height: 400px;
    overflow-y: auto;
    z-index: 100;
  }
  
  
  .notification-dropdown ul {
    list-style-type: none;
    padding: 15px;
    margin: 0;
  }
  
  .notification-item {
    padding: 10px;
    border-bottom: 1px solid #f1f1f1;
  }
  
  .notification-item.unread {
    background-color: #f9f9f9;
    font-weight: bold;
  }
  
  .notification-item .notification-message {
    display: block;
  }
  
  .notification-item .notification-time {
    font-size: 12px;
    color: #888;
  }
  
  
  
  .btn-login {
    background-color: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
  }
  .btn-login:hover {
    background-color: #0056b3;
  }
  
  /* Other styles for carousel, items, etc... */
  
  
  
  /* Main Content Section */
  
  /* Header Section */
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
  
    @media (max-width: 400px){
      margin-top: -20px; 
    }
  }
  
  .header input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
  }
  
  .header-center{
    position: relative;
    left: 600px;
  }
  
  .header button {
    padding: 10px;
    background: linear-gradient(45deg, #2e8b57, #042d86);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .header button:hover {
    background-color: #216c40;
  }
  
  /* Cart and Profile Section */
  .cart-profile-container {
    display: flex;
    align-items: center;
    gap: 20px;
  
    @media (max-width: 400px){
        position: relative;
         right: 50px;
  
    }
  }
  
  .cart {
    position: relative;
    display: flex;
    align-items: center;
  }
  
  .cart-icon {
    font-size: 24px;
    cursor: pointer;
  }
  
  
  
  .profile {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
  }
  
  .profile-details {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .profile-pic {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 2px solid #460eaf;
    object-fit: cover;
    transition: transform 0.2s;
  }
  
  .profile-pic:hover {
    transform: scale(1.1);
  }
  
  .profile-name {
    font-size: 1rem;
    font-weight: bold;
    color: #333;
  }
  
  .profile-dropdown-icon {
    font-size: 1rem;
    color: #666;
    transition: transform 0.3s;
  }
  
  .profile:hover .profile-dropdown-icon {
    transform: rotate(180deg);
  }
  
  .dropdown {
    position: absolute;
    top: 55px;
    right: 0;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 10px;
    display: none; /* Hidden by default */
    flex-direction: column;
    min-width: 150px;
    z-index: 20;
  }
  
  .profile:hover .dropdown {
    display: flex; /* Show dropdown on hover */
  }
  
  .dropdown a {
    text-decoration: none;
    color: #333;
    font-size: 0.9rem;
    padding: 10px;
    border-radius: 5px;
    transition: background-color 0.3s, color 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .dropdown a i {
    font-size: 1rem;
  }
  
  .dropdown a:hover {
    background: linear-gradient(45deg, #2e8b57, #042d86);
    color: #fff;
  }

    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="sidebar" id="sidebar">
        <h2 class="sidebar-logo">PrestoGrub</h2>
        <ul class="sidebar-menu">
              <li><i class="fas fa-home"></i> Home</li>
              <li><i class="fas fa-store"></i>Store</li>
              <li><i class="fas fa-bowl-food"></i>Foods</li>
              <li><i class="fas fa-file-invoice"></i>Cart</li>
              <li><i class="fas fa-wallet"></i>Order Status</li>
        </ul>
        <div class="sidebar-footer">
            <p>&copy; 2024 PrestoGrub</p>
        </div>
    </div>

    <button id="toggle-btn" class="toggle-btn">
        <i class="fas fa-bars"></i>
    </button>

    <div class="main-content">
        <div class="header">
            <div class="header-center">
                <form action="view_store.php" method="GET">
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

                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="profile">
                    <div class="profile-details">
                        <img src="img/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
                        <span class="profile-name"><?php echo htmlspecialchars($username); ?></span>
                        <i class="fas fa-chevron-down profile-dropdown-icon"></i>
                    </div>
                    <div class="dropdown">
                        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="function/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="login">
                    <a href="login.php" class="btn-login">Login</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

    <div class="store-container">
        <h2><?php echo htmlspecialchars($store['store_name']); ?> - Products</h2>
        
        <section class="items">
            <?php
            if ($product_result && $product_result->num_rows > 0) {
                while ($product = $product_result->fetch_assoc()) {
                    echo "<div class='item'>";
                    echo "<img src='productimg/" . htmlspecialchars($product["image"]) . "' alt='" . htmlspecialchars($product["name"]) . "'>";
                    echo "<div class='item-content'>";
                    echo "<h4>" . htmlspecialchars($product["name"]) . "</h4>";
                    echo "<p>" . htmlspecialchars($product["description"]) . "</p>";
                    echo "<p>Price: ₱" . htmlspecialchars($product["price"]) . "</p>";

                    // Check stock status
                    if ($product['out_of_stock'] == 1) {
                        echo "<button class='out-of-stock' disabled>Out of Stock</button>";
                    } else {
                        echo "<form action='function/add_to_order.php' method='post'>";
                        echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($product["product_id"]) . "'>";
                        echo "<button type='submit'>View Product</button>";
                        echo "</form>";
                    }

                    echo "</div>"; // Close item-content div
                    echo "</div>"; // Close product item div
                }
            } else {
                echo "<p>No products available for this store.</p>";
            }
            ?>
        </section>
    </div>

</body>
</html>
