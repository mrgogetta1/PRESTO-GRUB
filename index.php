<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connection/connection.php';
// Initialize $user_id as null
$user_id = null;

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check if user exists
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the password matches
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['isAdmin'] = $user['isAdmin'];

            // Redirect to home page or order status
            header("Location: index.php");
            exit;
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "User not found.";
    }
}



// Initialize variables
$product_result = null;
$store_result = null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = '%' . $search . '%';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';

// Set products per page to 8
$products_per_page = 8;

// Fetch the current page for pagination, default to 1 if not set
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

// If the search query exists, perform search operations
if (!empty($search)) {
    // Search for matching products
    $product_query = "
        SELECT * FROM products 
        WHERE name LIKE ?
        ORDER BY name
    ";
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bind_param("s", $search_param);
    if ($product_stmt->execute()) {
        $product_result = $product_stmt->get_result();
    } else {
        echo "Error executing product query: " . $product_stmt->error;
    }

    // Search for matching stores
    $store_query = "
        SELECT * FROM stores 
        WHERE store_name LIKE ? 
        ORDER BY store_name
    ";
    $store_stmt = $conn->prepare($store_query);
    $store_stmt->bind_param("s", $search_param);
    if ($store_stmt->execute()) {
        $store_result = $store_stmt->get_result();
    } else {
        echo "Error executing store query: " . $store_stmt->error;
    }
} else {
    // Fetch all products (fallback for when no search is performed)
    $all_products_query = "SELECT * FROM products ORDER BY name";
    $all_products_result = $conn->query($all_products_query);
    if (!$all_products_result) {
        echo "Error executing all products query: " . $conn->error;
    }

    // Fetch all stores (fallback for when no search is performed)
    $all_stores_query = "SELECT * FROM stores ORDER BY store_name";
    $all_stores_result = $conn->query($all_stores_query);
    if (!$all_stores_result) {
        echo "Error executing all stores query: " . $conn->error;
    }
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

// Fetch featured products
$featured_query = "SELECT * FROM products WHERE is_featured = 1 LIMIT 5";
$featured_products = $conn->query($featured_query);
if (!$featured_products) {
    echo "Error executing featured products query: " . $conn->error;
}

// Fetch most ordered products (based on completed orders)
$most_ordered_query = "
    SELECT p.*, COUNT(o.order_id) AS order_count
    FROM products p
    LEFT JOIN orders o ON p.product_id = o.product_id
    WHERE o.status = 'Complete' -- Only count completed orders
    GROUP BY p.product_id
    ORDER BY order_count DESC
    LIMIT 5
";
$most_ordered_result = $conn->query($most_ordered_query);
if (!$most_ordered_result) {
    echo "Error executing most ordered products query: " . $conn->error;
}

// Initialize variables for category and pagination
$category = isset($_GET['category']) ? $_GET['category'] : 'all'; // Default to 'all' if no category is selected
$products_per_page = 8;  // Number of products per page

// Pagination logic
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

// Fetch all categories for displaying in the menu
$categoriesResult = $conn->query("SELECT * FROM categories");

// Fetch filtered products based on selected category
if ($category !== 'all') {
    $filtered_products_query = "SELECT * FROM products WHERE category_id = (SELECT category_id FROM categories WHERE category_name = ?) LIMIT ?, ?";
    $filtered_products_stmt = $conn->prepare($filtered_products_query);
    $filtered_products_stmt->bind_param("sii", $category, $offset, $products_per_page);
    $filtered_products_stmt->execute();
    $filtered_products_result = $filtered_products_stmt->get_result();
} else {
    $filtered_products_query = "SELECT * FROM products LIMIT ?, ?";
    $filtered_products_stmt = $conn->prepare($filtered_products_query);
    $filtered_products_stmt->bind_param("ii", $offset, $products_per_page);
    $filtered_products_stmt->execute();
    $filtered_products_result = $filtered_products_stmt->get_result();
}

// Pagination count
$total_products_query = "SELECT COUNT(*) AS total FROM products WHERE category_id = (SELECT category_id FROM categories WHERE category_name = ?)";
$total_products_stmt = $conn->prepare($total_products_query);
$total_products_stmt->bind_param("s", $category);
$total_products_stmt->execute();
$total_products_result = $total_products_stmt->get_result();
$total_products = $total_products_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $products_per_page);


// Initialize variables
$store_result = null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = '%' . $search . '%';

// If the search query exists, perform search operations
if (!empty($search)) {
    // Search for matching stores
    $store_query = "
        SELECT * FROM stores 
        WHERE store_name LIKE ? 
        ORDER BY store_name
    ";
    $store_stmt = $conn->prepare($store_query);
    $store_stmt->bind_param("s", $search_param);
    if ($store_stmt->execute()) {
        $store_result = $store_stmt->get_result();
    } else {
        echo "Error executing store query: " . $store_stmt->error;
    }
} else {
    // Fetch all stores (fallback for when no search is performed)
    $all_stores_query = "SELECT * FROM stores ORDER BY store_name";
    $all_stores_result = $conn->query($all_stores_query);
    if ($all_stores_result) {
        $store_result = $all_stores_result; // Use this result for store display
    } else {
        echo "Error executing all stores query: " . $conn->error;
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="uploads/chef-hat.png" type="image/svg+xml">
    <title>Presto Grub</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/header.css">
</head>

<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
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
                <!-- Rest of your header content -->
            </div>

            <div class="cart-profile-container" id="cart-profile-container">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Notification Icon with Unread Count -->
                    <div class="notification-icon">
                        <i class="fa fa-bell"></i>
                        <span class="notification-count"><?php echo $unreadCount; ?></span>
                    </div>

                    <!-- Dropdown for Notifications -->
                    <div class="notification-dropdown">
                        <ul>
                            <?php while ($notification = $notifications->fetch_assoc()): ?>
                                <li class="notification-item <?php echo ($notification['is_read'] == 0) ? 'unread' : ''; ?>">
                                    <span class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></span>
                                    <span class="notification-time"><?php echo htmlspecialchars($notification['created_at']); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- Cart Icon -->
                    <div class="cart">
                        <i class="fas fa-shopping-cart cart-icon"></i>
                    </div>
                <?php endif; ?>

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


        <div class="main-border">
            <!-- Carousel and Other Sections -->
            <div class="carousel-container">
                <!-- Carousel Section -->
                <div class="carousel">
                    <button class="carousel-button left">&#x3c;</button>
                    <div class="carousel-images">
                        <img src="uploads/07b7e99bb01cca8732387d18919b2b4e.jpg" alt="Dish 1">
                        <img src="uploads/1.jpg" alt="Pizza">
                        <img src="uploads/124751a8d70d02adb4c829a101270d00.jpg" alt="Wine">
                    </div>
                    <button class="carousel-button right">&#x3e;</button>
                    <div class="carousel-indicators">
                        <span class="indicator active" data-slide="0"></span>
                        <span class="indicator" data-slide="1"></span>
                        <span class="indicator" data-slide="2"></span>
                    </div>
                </div>

                <!-- Image Section -->
                <div class="carousel-side-image">
                    <img src="uploads/emailad.png" alt="Side Image">
                </div>
            </div>

            <div class="recommendations">
                <div class="recommendations-header">
                    <h2>MOST SOLD PRODUCTS</h2>
                </div>
                <div class="order-items">
                    <?php if ($most_ordered_result && $most_ordered_result->num_rows > 0): ?>
                        <?php while ($most_ordered_product = $most_ordered_result->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-card">
                                    <!-- Product Image -->
                                    <img src="productimg/<?php echo htmlspecialchars($most_ordered_product['image']); ?>"
                                        alt="<?php echo htmlspecialchars($most_ordered_product['name']); ?>" class="product-image">

                                    <!-- Total Sold -->
                                    <div class="total-sold">
                                        <p>TOTAL SOLD: <span><?php echo number_format($most_ordered_product['order_count']); ?></span></p>
                                    </div>

                                    <!-- View Product Button -->
                                    <a href="view_product.php?product_id=<?php echo htmlspecialchars($most_ordered_product['product_id']); ?>" class="view-product-btn">View Product</a>

                                    <!-- Product Title -->
                                    <div class="product-details">
                                        <h3 class="product-title"><?php echo htmlspecialchars($most_ordered_product['name']); ?></h3>

                                        <!-- Product Description -->
                                        <p class="product-description"><?php echo htmlspecialchars($most_ordered_product['description']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No most ordered products available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Menu and Order Items Section Merged -->
            <div class="menu-container">
                <div class="menu-header">
                    <h2>MENU CATEGORY</h2>
                </div>
                <div class="menu-items">
                    <!-- Display 'All Menus' option -->
                    <a href="index.php?category=all">
                        <div class="menu-card">🍴<p>All Menus</p>
                        </div>
                    </a>

                    <!-- Dynamically display categories -->
                    <?php while ($category_row = $categoriesResult->fetch_assoc()): ?>
                        <a href="index.php?category=<?= urlencode($category_row['category_name']); ?>">
                            <div class="menu-card">🍽️<p><?= htmlspecialchars($category_row['category_name']); ?></p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>

                <!-- Display filtered products -->
                <div class="order-items menu-cat">
                    <?php if ($filtered_products_result && $filtered_products_result->num_rows > 0): ?>
                        <?php while ($product = $filtered_products_result->fetch_assoc()): ?>
                            <div class="product-card" data-name="<?php echo htmlspecialchars($product['name']); ?>">
                                <!-- Product Image -->
                                <img src="productimg/<?php echo htmlspecialchars($product['image']); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    class="product-image">

                                <!-- Product Title -->
                                <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>

                                <!-- Product Description -->
                                <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>

                                <!-- Product Price -->
                                <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>

                                <!-- View Product Button -->
                                <a href="view_product.php?product_id=<?php echo htmlspecialchars($product['product_id']); ?>"
                                    class="view-product-btn">View Product</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No products available in this category.</p>
                    <?php endif; ?>
                </div>

                <!-- Pagination Controls Merged Inside -->
                <div class="pagination-controls">
                    <?php if ($current_page > 1): ?>
                        <a href="?category=<?php echo $category; ?>&page=<?php echo $current_page - 1; ?>" class="pagination-btn prev-btn">&lt;</a>
                    <?php else: ?>
                        <span class="pagination-btn prev-btn disabled">&lt;</span>
                    <?php endif; ?>

                    <span class="page-info">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?category=<?php echo $category; ?>&page=<?php echo $current_page + 1; ?>" class="pagination-btn next-btn">&gt;</a>
                    <?php else: ?>
                        <span class="pagination-btn next-btn disabled">&gt;</span>
                    <?php endif; ?>
                </div>
            </div>


            <div class="store-container">
                <div class="store-header">
                    <h2>ALL STORE</h2>
                </div>
                <div class="store-items">
                    <?php if ($store_result && $store_result->num_rows > 0): ?>
                        <?php while ($store = $store_result->fetch_assoc()): ?>
                            <div class="store-card">
                                <!-- Display store image first -->
                                <div class="store-image">
                                    <?php if (!empty($store['store_image'])): ?>
                                        <img src="store_images/<?php echo htmlspecialchars($store['store_image']); ?>" alt="Store Image" class="store-img">
                                    <?php else: ?>
                                        <img src="path/to/default-image.jpg" alt="Default Store Image" class="store-img">
                                    <?php endif; ?>
                                </div>

                                <!-- Store name comes second -->
                                <h4 class="store-name"><?php echo htmlspecialchars($store['store_name']); ?></h4>

                                <!-- View store button -->
                                <a href="view_store.php?store_id=<?php echo htmlspecialchars($store['store_id']); ?>" class="view-store-btn">View Store</a>

                                <!-- Display store location and description if needed -->
                                <p class="store-location">
                                    <?php echo isset($store['store_location']) ? htmlspecialchars($store['store_location']) : 'Location not provided'; ?>
                                </p>

                                <p class="store-description">
                                    <?php echo isset($store['store_description']) ? htmlspecialchars($store['store_description']) : 'No description available'; ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No stores available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div> <!-- End of store-container -->
            <br>
            <br>
            <!-- Footer -->
            <?php include 'footer.php'; ?>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchBtn = document.querySelector('.search-btn');
                    const searchForm = document.querySelector('.search-form');
                    const cartprofile = document.querySelector('#cart-profile-container');

                    searchBtn.addEventListener('click', function() {
                        searchForm.classList.toggle('active');
                        cartprofile.classList.toggle('hide');
                        this.classList.toggle('active');
                        this.innerHTML = this.classList.contains('active') ? '<i class="fa fa-times"></i>' : '<i class="fa fa-search"></i>';
                    });
                });

                document.addEventListener('DOMContentLoaded', function() {
                    const toggleBtn = document.getElementById('toggle-btn');
                    const sidebar = document.getElementById('sidebar');
                    const mainContent = document.querySelector('.main-content');

                    toggleBtn.addEventListener('click', function() {
                        sidebar.classList.toggle('active');
                        mainContent.classList.toggle('shifted');
                    });
                });


                // Toggle the notification dropdown visibility when the notification icon is clicked
                document.querySelector('.notification-icon').addEventListener('click', function() {
                    const dropdown = document.querySelector('.notification-dropdown');
                    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';

                    // Close the notification dropdown if you click outside of it
                    document.addEventListener('click', function(event) {
                        const dropdown = document.querySelector('.notification-dropdown');
                        const notificationIcon = document.querySelector('.notification-icon');

                        // Check if the click is outside the dropdown and the icon
                        if (!dropdown.contains(event.target) && !notificationIcon.contains(event.target)) {
                            dropdown.style.display = 'none';
                        }
                    });

                    // Mark notifications as read
                    fetch('seller/mark_notifications_read.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update notification item styles (remove "unread" class)
                                document.querySelectorAll('.notification-item').forEach(item => {
                                    item.classList.remove('unread');
                                });

                                // Optionally, update notification count
                                document.querySelector('.notification-count').style.display = 'none'; // Hide count after read
                            }
                        });
                });



                document.addEventListener('DOMContentLoaded', function() {
                    const searchQuery = new URLSearchParams(window.location.search).get('search')?.toLowerCase();

                    if (searchQuery) {
                        // Get all product items in the order items section
                        const orderItems = document.querySelectorAll('.order-card');

                        // Loop through each item and check if it matches the search query
                        orderItems.forEach(item => {
                            const productName = item.getAttribute('data-name').toLowerCase();

                            // If the product name doesn't match the search query, hide it
                            if (!productName.includes(searchQuery)) {
                                item.style.display = 'none';
                            } else {
                                item.style.display = 'block';
                            }
                        });
                    }
                });

                const urlParams = new URLSearchParams(window.location.search);
                const selectedCategory = urlParams.get('category') || 'all';

                document.querySelectorAll('.menu-card').forEach(card => {
                    const categoryText = card.querySelector('p').textContent.trim().toLowerCase();
                    if (categoryText === selectedCategory.toLowerCase()) {
                        card.classList.add('active');
                    } else {
                        card.classList.remove('active');
                    }
                });



                const searchQuery = new URLSearchParams(window.location.search).get('search');

                const allProducts = document.getElementById("allProducts");
                const allStores = document.getElementById("allStores");
                const searchProducts = document.getElementById("searchProducts");
                const searchStores = document.getElementById("searchStores");

                if (searchQuery) {
                    allProducts.style.display = 'none';
                    allStores.style.display = 'none';
                }

                // Redirect back to all products and stores after 15 seconds of inactivity
                setTimeout(function() {
                    if (searchQuery) {
                        window.location.href = window.location.pathname;
                    }
                }, 15000);


                // Profile dropdown toggle
                const profile = document.querySelector('.profile');
                const dropdown = document.querySelector('.dropdown');
                profile.addEventListener('click', () => {
                    dropdown.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!profile.contains(e.target)) {
                        dropdown.classList.remove('active');
                    }
                });

                // Carousel for images
                const carouselImages = document.querySelector('.carousel-images');
                const images = document.querySelectorAll('.carousel-images img');
                const prevButton = document.querySelector('.carousel-button.left');
                const nextButton = document.querySelector('.carousel-button.right');
                console.log('Prev Button:', prevButton);
                console.log('Next Button:', nextButton);

                prevButton.addEventListener('click', () => {
                    console.log('Prev Button Clicked');
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    updateCarousel();
                });

                nextButton.addEventListener('click', () => {
                    console.log('Next Button Clicked');
                    currentIndex = (currentIndex + 1) % images.length;
                    updateCarousel();
                });

                let currentIndex = 0;

                function updateCarousel() {
                    const offset = -currentIndex * 100; // Move by 100% of container width
                    carouselImages.style.transform = `translateX(${offset}%)`;
                }

                nextButton.addEventListener('click', () => {
                    currentIndex = (currentIndex + 1) % images.length;
                    console.log('Next: ', currentIndex); // Debugging
                    updateCarousel();
                });

                prevButton.addEventListener('click', () => {
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    console.log('Prev: ', currentIndex); // Debugging
                    updateCarousel();
                });

                function autoSlide() {
                    currentIndex = (currentIndex + 1) % images.length;
                    console.log('AutoSlide: ', currentIndex);
                    updateCarousel();
                }

                setInterval(autoSlide, 3000);
                updateCarousel();


                document.addEventListener('DOMContentLoaded', () => {
                    const profile = document.querySelector('.profile');
                    const dropdown = document.querySelector('.dropdown');

                    if (profile && dropdown) {
                        profile.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdown.classList.toggle('active');
                        });

                        // Close dropdown when clicking outside
                        document.addEventListener('click', (event) => {
                            if (!profile.contains(event.target)) {
                                dropdown.classList.remove('active');
                            }
                        });
                    } else {
                        console.error("Profile or dropdown elements not found.");
                    }
                });






                // Handle the "See More" button functionality
                const seeMoreButton = document.getElementById('see-more');
                const moreItems = document.querySelectorAll('.more-item');
                seeMoreButton.addEventListener('click', () => {
                    moreItems.forEach(item => {
                        item.style.display = 'block'; // Show hidden items
                    });
                    seeMoreButton.style.display = 'none'; // Hide the "See More" button
                });

                // Recommended items carousel
                const recommendedCarouselImages = document.querySelector('#recommended-items');
                const recommendedItems = document.querySelectorAll('#recommended-items .item');
                const prevRecommendedButton = document.querySelector('.recommendations .carousel-button.left');
                const nextRecommendedButton = document.querySelector('.recommendations .carousel-button.right');
                let currentRecommendedIndex = 0;

                function updateRecommendedCarousel() {
                    const offset = -currentRecommendedIndex * 200;
                    recommendedCarouselImages.style.transform = `translateX(${offset}px)`;
                }

                nextRecommendedButton.addEventListener('click', () => {
                    currentRecommendedIndex = (currentRecommendedIndex + 1) % recommendedItems.length;
                    updateRecommendedCarousel();
                });

                prevRecommendedButton.addEventListener('click', () => {
                    currentRecommendedIndex = (currentRecommendedIndex - 1 + recommendedItems.length) % recommendedItems.length;
                    updateRecommendedCarousel();
                });
            </script>

</body>

</html>