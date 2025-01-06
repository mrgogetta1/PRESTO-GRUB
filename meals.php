<?php
session_start();
require_once 'connection/connection.php';
// Initialize $user_id as null
$user_id = null;

// Check if the user is logged in by checking if session variable 'user_id' is set
// Check if the user is logged in and fetch the profile picture
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT username, profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();

    if ($user) {
        $username = $user['username'];
        $profile_picture = $user['profile_picture'] ?? null; // Use null coalescing operator to set default
    } else {
        $username = null;
        $profile_picture = null;
    }
}

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
<html>
<head>
    <title>Presto Grub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/Meals.css">
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 160px;
        }

        .page-link {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .page-link:hover {
            background-color: #0056b3;
        }

        .page-link.active {
            background-color: #28a745;
        }

        .page-link:disabled {
            background-color: #ccc;
            pointer-events: none;
        }

        .view-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .view-button:hover {
            background-color: #218838;
        }


        .footer {
            background: linear-gradient(45deg, #2e8b57, #042d86);
            color: #fff;
            padding: 18px 20px;
            font-family: Arial, sans-serif;
            position: relative;
            left: 120px;
            margin: 0 130px !important; /* Remove any extra margin */
        }

        .about-company h3 {
            font-size: 25px;
            color: white;
            font-weight: 800;
            margin-top: 60px !important; /* Remove margin to bring it closer to the map */
            padding-top: 0; /* Remove any padding on top */

        }

        @media (max-width: 1024px) {
            .footer {
                top: -260px;
            }
        }

        @media (max-width: 800px) {
            .footer {
                top: -260px;
                width: 600px;
            }
        }

    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <h2 class="sidebar-logo">PrestoGrub</h2>
        <ul class="sidebar-menu">
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="stores.php"><i class="fas fa-store"></i> Stores</a></li>
            <li><a href="meals.php"><i class="fas fa-utensils"></i> Food</a></li>
            <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
            <li><a href="order_status.php"><i class="fas fa-receipt"></i> Order Status</a></li>
          </ul>
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
                <form action="meals.php" method="GET">
                    <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="cart-profile-container">
                <!-- <div class="notification-icon">
                    <i class="fa fa-bell"></i>  
                </div> -->
                <div class="cart">
                    <i class="fas fa-shopping-cart cart-icon"></i>
                </div>
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
            <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
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

        <h2>All Foods</h2> <!-- Title displayed above the section -->
        <section class="items">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $avg_rating = round($row['avg_rating'], 1);
                $total_reviews = $row['total_reviews'];

                echo "<div class='item'>";
                echo "<img src='productimg/" . htmlspecialchars($row["image"]) . "' alt='" . htmlspecialchars($row["name"]) . "'>";
                echo "<h4>" . htmlspecialchars($row["name"]) . "</h4>";
                echo "<p class='price'>₱ " . htmlspecialchars($row["price"]) . "</p>";
                echo "<p>" . htmlspecialchars($row["description"]) . "</p>";
                echo "<a href='view_product.php?product_id=" . htmlspecialchars($row["product_id"]) . "' class='view-button'>View Product</a>";
                echo "</div>";
            }
        } else {
            echo "<p>No products available.</p>";
        }

        $conn->close();
        ?>
        </section>

        <div class="pagination">
        <?php
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($page == $i) ? 'active' : '';
            echo "<a href='meals.php?page=$i&search=" . urlencode($search_query) . "' class='page-link $active_class'>$i</a>";
        }
        ?>
        </div>
    </div>


    <!-- Footer -->
<?php include 'footer.php'; ?>

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
    </script>

</body>
</html>
