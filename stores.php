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

// Pagination setup
$limit = 8; // Number of stores per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $limit; // Calculate the offset

// Search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// SQL to count total stores (filtered by search if applicable)
$sql_count = "SELECT COUNT(*) AS total FROM stores WHERE store_name LIKE '%$search_query%'";
$result_count = $conn->query($sql_count);
$total_stores = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_stores / $limit); // Calculate the total pages

// SQL to fetch stores for the current page (filtered by search if applicable)
$sql = "SELECT * FROM stores WHERE store_name LIKE '%$search_query%' ORDER BY store_name ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Check for SQL execution errors
if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Presto Grub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/store.css">
    <link rel="stylesheet" type="text/css" href="css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="uploads/chef-hat.png" type="image/svg+xml">

    <style>
        .footer {
            background: linear-gradient(45deg, #2e8b57, #042d86);
            color: #fff;
            padding: 18px 20px;
            font-family: Arial, sans-serif;
            position: relative;
            left: 120px;
            margin: 0 130px !important;
            /* Remove any extra margin */
        }

        .about-company h3 {
            font-size: 25px;
            color: white;
            font-weight: 800;
            margin-top: 60px !important;
            /* Remove margin to bring it closer to the map */
            padding-top: 0;
            /* Remove any padding on top */

        }


        /* Hide the sidebar and show the toggle button when screen width <= 400px */
        @media (max-width: 400px) {
            /* .sidebar {
    left: -200px; 
  } */

            .toggle-btn {
                display: block;
                /* Show toggle button */
                position: fixed;
                top: 10px;
                font-size: 1.2rem;
                left: -20px;
                z-index: 15;
            }

            .main-content {
                margin-left: 0;
                /* Remove margin when sidebar is hidden */
            }

            .main-content.shifted {
                margin-left: 200px;
                /* Adjust when sidebar is visible */
            }

            /* .sidebar-menu li {
    position: relative;
    top: -640px !important;
  } */

        }


        @media (max-width: 1024px) {

            .footer-content {
                width: 400px;
                position: relative;
                left: 200px;
                gap: 200px;
            }

            .contact-info {
                margin-top: 30px;
            }

        }

        @media (max-width: 860px) {

            .footer-content {
                width: 350px;
                position: relative;
                left: 150px;
                gap: 200px;
            }

            .contact-info {
                margin-top: 30px;
            }

        }

        @media (max-width: 750px) {


            .footer {
                width: 80%;
            }

            .footer-content {
                width: 100%;
                position: relative;
                left: 150px;
                gap: 100px;
            }

            .about-company {
                padding-right: 150px;
            }

            .contact-info {
                margin-top: 30px;
            }

        }

        @media (max-width: 640px) {

            .footer {
                width: 75%;
            }

            .footer-content {
                width: 350px;
                position: relative;
                display: block !important;
            }


            .contact-info {
                margin-top: 30px;
                display: block !important;
            }

            .about-company {
                display: block;
                position: relative;
                right: 100px;
            }

        }

        @media (max-width: 540px) {
            /* 
    .footer {
        width: 590px;
        background: linear-gradient(45deg, #2e8b57, #042d86);
        color: #fff;
        padding: 18px 20px;
        font-family: Arial, sans-serif;
        position: relative;
        left: -110px;
        top: -300px;
        margin: 0 130px !important;
    } */



        }

        @media (max-width: 490px) {
            /* .sidebar {
    left: -200px;
  }

  .sidebar-logo {
    position: relative;
    top: 60px;
  }

  .toggle-btn {
    display: block; 
    position: fixed;
    top: 10px;
    font-size: 1.2rem;
    left: -20px;
    z-index: 15;
  } */

            .main-content {
                margin-left: 0;
                /* Remove margin when sidebar is hidden */
            }

            .main-content.shifted {
                margin-left: 200px;
            }

            /* .sidebar-menu li {
    position: relative;
    top: -340px !important;
    font-size: 1.2rem;
  } */

        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>


    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="header-center">
                <div class="search-form">
                    <form action="index.php" method="GET">
                        <input type="text" name="search" placeholder="Search">
                        <button type="submit">Search</button>
                    </form>
                </div>
                <button class="search-btn"><i class="fa fa-search"></i></button>
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



        <h2>All Stores</h2> <!-- Title displayed above the section -->
        <section class="items">
            <?php
            // Display stores for the current page
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='item'>";
                    echo "<img src='store_images/" . htmlspecialchars($row["store_image"]) . "' alt='" . htmlspecialchars($row["store_name"]) . "'>";
                    echo "<h4>" . htmlspecialchars($row["store_name"]) . "</h4>";
                    echo "<a href='view_store.php?store_id=" . urlencode($row["store_id"]) . "'><button>View Store</button></a>";
                    echo "</div>";
                }
            } else {
                echo "<p>No stores found matching your search.</p>";
            }
            ?>
        </section>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="index.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>" class="pagination-btn">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="index.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>" class="pagination-btn <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="index.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>" class="pagination-btn">Next</a>
            <?php endif; ?>
        </div>

    </div>


    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('shifted');
            });
        });
    </script>

</body>

</html>