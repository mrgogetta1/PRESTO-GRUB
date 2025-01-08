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


// Initialize $search_query to avoid undefined variable warnings
$search_query = isset($_GET['search']) ? $_GET['search'] : '';



// Check if the product_id is set and valid
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
} else {
    die("Product ID is missing or invalid.");
}

// Fetch product details
$product_query = "SELECT * FROM products WHERE product_id = $product_id";
$product_result = $conn->query($product_query);

if ($product_result->num_rows > 0) {
    $product = $product_result->fetch_assoc();
} else {
    die("Product not found.");
}

// Fetch product variants based on product_id
$variant_query = "SELECT pv.variant_id, pv.variant_name, pv.sku, pv.price AS variant_price, pv.stock_quantity
                  FROM product_variants pv
                  WHERE pv.product_id = $product_id";
$variant_result = $conn->query($variant_query);

// If variants exist, display them
$variants = [];
if ($variant_result->num_rows > 0) {
    while ($variant = $variant_result->fetch_assoc()) {
        $variants[] = $variant;
    }
}

// Fetch user details if logged in
$user_details = [];
$hasPurchased = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT first_name, last_name, email, contact_no FROM users WHERE id = $user_id";
    $user_result = $conn->query($user_query);

    if ($user_result->num_rows > 0) {
        $user_details = $user_result->fetch_assoc();
    }

    // Check if the user has purchased this product
    $purchase_query = "SELECT * FROM orders WHERE user_id = $user_id AND product_id = $product_id AND status = 'completed'";
    $purchase_result = $conn->query($purchase_query);

    if ($purchase_result->num_rows > 0) {
        $hasPurchased = true;
    }
}



if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $variant_id = isset($_POST['variant_id']) && $_POST['variant_id'] != '' ? (int)$_POST['variant_id'] : null;
    $total_price = $product['price'] * $quantity;
    $payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : 'cash_on_delivery';

    // Fetch variant price if a variant is selected
    $variant_price = 0;
    if ($variant_id !== null) {
        $variant_query = "SELECT price FROM product_variants WHERE variant_id = $variant_id";
        $variant_result = $conn->query($variant_query);
        if ($variant_result->num_rows > 0) {
            $variant = $variant_result->fetch_assoc();
            $variant_price = $variant['price'];
        }
    }

    // Calculate total price (product price + variant price) * quantity
    $total_price = ($product['price'] + $variant_price) * $quantity;

    // Check if the product already exists in the cart
    $check_query = "SELECT * FROM cart 
                    WHERE user_id = $user_id 
                      AND product_id = $product_id 
                      AND (variant_id = " . ($variant_id !== null ? $variant_id : "NULL") . " 
                           OR (variant_id IS NULL AND " . ($variant_id === null ? "TRUE" : "FALSE") . "))";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        // Update quantity if the product is already in the cart
        $existing_item = $check_result->fetch_assoc();
        $new_quantity = $existing_item['quantity'] + $quantity;
        $update_query = "UPDATE cart SET quantity = $new_quantity WHERE cart_id = " . $existing_item['cart_id'];

        if ($conn->query($update_query) === TRUE) {
            // Notify user
            echo "<script>alert('Product quantity updated in cart!'); window.location = 'order.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // Insert a new product into the cart
        $insert_query = "INSERT INTO cart (user_id, product_id, variant_id, quantity, payment_method) 
                         VALUES ($user_id, $product_id, " . ($variant_id !== null ? $variant_id : "NULL") . ", $quantity, '$payment_method')";

        if ($conn->query($insert_query) === TRUE) {
            // Fetch the seller's user ID
            $seller_query = "SELECT s.user_id AS seller_id FROM products p 
                             JOIN stores s ON p.store_id = s.store_id 
                             WHERE p.product_id = $product_id";
            $seller_result = $conn->query($seller_query);

            if ($seller_result->num_rows > 0) {
                $seller = $seller_result->fetch_assoc();
                $seller_id = $seller['seller_id'];

                // Add a notification for the seller
                $product_name = htmlspecialchars($product['name']);
                $message = "A product has been added to the cart: $product_name by user ID $user_id.";
                $created_at = date('Y-m-d H:i:s');
                $notification_query = "INSERT INTO notifications (user_id, message, created_at) 
                                       VALUES ($seller_id, '$message', '$created_at')";
                $conn->query($notification_query);
            }

            // Notify user
            echo "<script>alert('Product added to cart!'); window.location = 'order.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}



// Handle the "Proceed to Checkout" when the Buy Now modal is confirmed
if (isset($_POST['buy_now'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
    $total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : $product['price'];

    // Fetch variant price if selected
    if ($variant_id > 0) {
        $variant_query = "SELECT price FROM product_variants WHERE variant_id = $variant_id";
        $variant_result = $conn->query($variant_query);
        if ($variant_result->num_rows > 0) {
            $variant = $variant_result->fetch_assoc();
            $total_price += $variant['price'];
        }
    }

    $total_price *= $quantity;
    $payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : 'cash_on_delivery';

    // Insert into orders table
    $insert_order_query = "INSERT INTO orders (user_id, product_id, variant_id, quantity, total_price, payment_method, status, order_date) 
                           VALUES ($user_id, $product_id, $variant_id, $quantity, $total_price, '$payment_method', 'Checked Out', NOW())";

    if ($conn->query($insert_order_query) === TRUE) {
        $order_id = $conn->insert_id; // Get the last inserted order ID

        // Fetch the seller's user ID from the product details
        $seller_id_query = "SELECT s.user_id AS seller_id FROM products p JOIN stores s ON p.store_id = s.store_id WHERE p.product_id = $product_id";
        $seller_result = $conn->query($seller_id_query);

        if ($seller_result->num_rows > 0) {
            $seller = $seller_result->fetch_assoc();
            $seller_id = $seller['seller_id'];

            // Prepare the message to send to the seller
            $product_name = htmlspecialchars($product['name']);
            $product_price = htmlspecialchars($product['price']);
            $message = "New Order:\nProduct: $product_name\nQuantity: $quantity\nPrice: ₱$product_price\nStatus: Checked Out";

            // Insert the message into the chat_messages table
            $message_query = "INSERT INTO chat_messages (sender_id, recipient_id, message, timestamp, is_read, order_id, type) 
                              VALUES (?, ?, ?, NOW(), 0, ?, 'system')";
            $stmt = $conn->prepare($message_query);
            $stmt->bind_param("iisi", $user_id, $seller_id, $message, $order_id);

            if ($stmt->execute()) {
                // Insert notification to seller
                $notification_query = "INSERT INTO notifications (user_id, message, created_at) 
                                       VALUES (?, ?, NOW())";
                $notification_stmt = $conn->prepare($notification_query);
                $notification_stmt->bind_param("is", $seller_id, $message);
                $notification_stmt->execute();

                echo "<script>alert('Order confirmed and message sent to the seller!'); window.location = 'order_status.php';</script>";
            } else {
                echo "Error sending message: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error: Seller not found.";
        }
    } else {
        echo "Error: " . $conn->error;
    }
}



// Handle the comment submission
if (isset($_POST['submit_comment'])) {
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $username = $user_details['first_name'] . ' ' . $user_details['last_name'];

    $insert_comment_query = "INSERT INTO product_comments (user_id, product_id, username, comment, rating) 
                             VALUES ($user_id, $product_id, '$username', '$comment', $rating)";

    if ($conn->query($insert_comment_query) === TRUE) {
        echo "<script>alert('Comment submitted!');</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="uploads/chef-hat.png" type="image/svg+xml">
    <link rel="stylesheet" href="css/view_product.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/sidebar.css">



    <style>
        /* Hide the sidebar and show the toggle button when screen width <= 400px */
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
            <div class="cart-profile-container" id="cart-profile-container">
                <div class="notification-icon">
                    <i class="fa fa-bell"></i>
                </div>
                <div class="cart">
                    <i class="fas fa-shopping-cart cart-icon"></i>
                </div>

                <!-- Profile Dropdown -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="profile">
                        <div class="profile-details">
                            <img src="img/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
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
                    <div class="login">
                        <a href="login.php" class="btn-login">Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="container">
            <div class="product-container">
                <div class="product-details">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="productimg/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" />
                        <?php else: ?>
                            <img src="default-image.jpg" alt="No Image Available" />
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="price-info">
                            <p class="price">₱<?php echo htmlspecialchars($product['price']); ?></p>
                        </div>

                        <h3>Choose Variants</h3>

                        <form id="variant-form" method="POST">
                            <?php
                            // Fetch variants for this product
                            $variant_query = "SELECT * FROM product_variants WHERE product_id = $product_id";
                            $variant_result = $conn->query($variant_query);

                            $variants = [];
                            while ($variant = $variant_result->fetch_assoc()) {
                                $variants[$variant['variant_name']][] = $variant;
                            }

                            // Display each variant group (e.g. Flavor, Size)
                            foreach ($variants as $variant_name => $variant_group) {
                                // Sanitize variant name for use in HTML attribute
                                $sanitized_variant_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $variant_name);

                                echo "<div class='variant-group'>";
                                echo "<h4>$variant_name</h4>";

                                // Display SKU radio buttons for each variant name (radio button behavior for one selection)
                                foreach ($variant_group as $variant) {
                                    $sku = htmlspecialchars($variant['sku']);
                                    $price = number_format($variant['price'], 2);
                                    echo "<div class='variant-option'>";
                                    echo "<input type='radio' name='variant_{$sanitized_variant_name}' class='variant-radio' 
                data-price='$price' data-variant-id='" . $variant['variant_id'] . "' 
                id='sku_" . $variant['variant_id'] . "' value='" . $variant['variant_id'] . "' />";
                                    echo "<label for='sku_" . $variant['variant_id'] . "'>";
                                    echo "$sku (₱$price)";
                                    echo "</label>";
                                    echo "</div>";
                                }

                                echo "</div>";
                            }
                            ?>
                        </form>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="quantity-controls">
                                <label for="quantity">Quantity:</label>
                                <div class="quantity-buttons">
                                    <button type="button" onclick="decreaseQuantity()">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" readonly>
                                    <button type="button" onclick="increaseQuantity()">+</button>
                                </div>
                            </div>
                            <p class="total-quan">Total Quantity Price: <span id="totalPrice">₱<?php echo $product['price']; ?></span></p>

                            <!-- Add to Cart Button -->
                            <form method="POST" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
                                <input type="hidden" name="variant_id" value="" id="selected-variant-id" /> <!-- Default is empty -->
                                <input type="hidden" name="quantity" value="1" id="quantity-input" />
                                <button class="btn add-to-cart" name="add_to_cart" type="submit">Add to Cart</button>
                            </form>


                            <!-- Updated Buy Now Button -->
                            <!-- Updated Buy Now Button -->
                            <form method="POST" action="delivery_form.php">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
                                <input type="hidden" name="variant_id" value="0" id="selected-variant-id-buy-now" />
                                <input type="hidden" name="quantity" value="1" id="quantity-input-buy-now" />
                                <input type="hidden" name="payment_method" value="cash_on_delivery" />
                                <button class="btn buy-now" name="buy_now" type="submit">Buy Now</button>
                            </form>




                        <?php else: ?>
                            <p class="login-prompt">Please log in to purchase.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="user-comments">
                <h3>Customer Comments</h3>
                <?php
                $comment_query = "SELECT * FROM product_comments WHERE product_id = $product_id ORDER BY date_posted DESC";
                $comments_result = $conn->query($comment_query);

                while ($comment = $comments_result->fetch_assoc()) {
                    echo '<div class="comment">';
                    echo '<div class="comment-header">';
                    echo '<strong>' . htmlspecialchars($comment['username']) . '</strong>';
                    echo '<span class="comment-date">' . htmlspecialchars($comment['date_posted']) . '</span>';
                    echo '</div>';
                    echo '<div class="comment-rating">Rating: ' . str_repeat('★', $comment['rating']) . '</div>';
                    echo '<p>' . htmlspecialchars($comment['comment']) . '</p>';
                    echo '</div>';
                }
                ?>
                <div class="user-rate">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" class="comment-form">
                            <textarea name="comment" placeholder="Add your comment here..." required></textarea>
                            <h3>Customer Rate</h3>
                            <label for="rating">Rating:</label>
                            <div class="rating-stars">
                                <input type="hidden" id="rating-value" name="rating" value="5" required>
                                <div class="stars" id="star-container">
                                    <i class="fa fa-star" data-value="1"></i>
                                    <i class="fa fa-star" data-value="2"></i>
                                    <i class="fa fa-star" data-value="3"></i>
                                    <i class="fa fa-star" data-value="4"></i>
                                    <i class="fa fa-star" data-value="5"></i>
                                </div>
                            </div>
                            <button type="submit" name="submit_comment" class="btn submit-comment">Submit Comment</button>
                        </form>
                    <?php else: ?>
                        <p class="login-prompt">Please log in to leave a comment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include 'footer.php'; ?>


        <script src="js/view_product.js"></script>

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


            document.addEventListener("DOMContentLoaded", function() {
                const stars = document.querySelectorAll("#star-container i");
                const ratingValue = document.getElementById("rating-value");

                stars.forEach((star) => {
                    // Highlight stars on hover
                    star.addEventListener("mouseover", () => {
                        const value = star.getAttribute("data-value");
                        highlightStars(value);
                    });

                    // Reset stars when not hovering
                    star.addEventListener("mouseout", () => {
                        highlightStars(ratingValue.value); // Show current rating
                    });

                    // Update the rating value on click
                    star.addEventListener("click", () => {
                        const value = star.getAttribute("data-value");
                        ratingValue.value = value;
                        highlightStars(value);
                    });
                });

                // Function to highlight stars up to a specific value
                function highlightStars(value) {
                    stars.forEach((star) => {
                        const starValue = star.getAttribute("data-value");
                        star.classList.toggle("active", starValue <= value);
                    });
                }

                // Initialize stars with default rating value
                highlightStars(ratingValue.value);
            });
            // JS for Quantity Controls
            function increaseQuantity() {
                var quantityInput = document.getElementById('quantity');
                quantityInput.value = parseInt(quantityInput.value) + 0;
                updateTotalPrice();
            }

            function decreaseQuantity() {
                var quantityInput = document.getElementById('quantity');
                if (parseInt(quantityInput.value) > 1) {
                    quantityInput.value = parseInt(quantityInput.value) - 0;
                    updateTotalPrice();
                }
            }

            // Function to update the total price based on selected variant and quantity
            function updateTotalPrice() {
                var selectedVariantPrice = 0;
                var quantity = document.getElementById('quantity').value;

                // Find selected variants (one SKU per variant_name)
                var selectedVariants = document.querySelectorAll('.variant-radio:checked');
                selectedVariants.forEach(function(checkbox) {
                    selectedVariantPrice += parseFloat(checkbox.getAttribute('data-price'));
                });

                var basePrice = <?php echo $product['price']; ?>; // Base product price
                var totalPrice = (basePrice + selectedVariantPrice) * quantity;

                // Update the total price on the page
                document.getElementById('totalPrice').innerText = totalPrice.toFixed(2);
            }

            // Add event listener to radio buttons to update price when selected
            var radios = document.querySelectorAll('.variant-radio');
            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    updateTotalPrice();
                    updateVariantId(radio);
                });
            });

            // Function to update the hidden variant_id input based on selected variant
            function updateVariantId(radio) {
                var selectedVariantId = radio.getAttribute('data-variant-id');

                // If the radio button is checked, update the corresponding hidden input
                document.getElementById('selected-variant-id').value = selectedVariantId;
                document.getElementById('selected-variant-id-buy-now').value = selectedVariantId;
            }

            // Ensure variant_id is updated if a variant is preselected (in case of page reload or default selection)
            document.addEventListener('DOMContentLoaded', function() {
                // Quantity Control
                const quantityInput = document.getElementById('quantity');
                const quantityInputBuyNow = document.getElementById('quantity-input-buy-now');
                const quantityInputAddToCart = document.getElementById('quantity-input');

                // Function to increase the quantity
                function increaseQuantity() {
                    let quantity = parseInt(quantityInput.value);
                    quantityInput.value = quantity + 1;
                    quantityInputBuyNow.value = quantity + 1;
                    quantityInputAddToCart.value = quantity + 1;
                    updateTotalPrice();
                }

                // Function to decrease the quantity
                function decreaseQuantity() {
                    let quantity = parseInt(quantityInput.value);
                    if (quantity > 1) {
                        quantityInput.value = quantity - 1;
                        quantityInputBuyNow.value = quantity - 1;
                        quantityInputAddToCart.value = quantity - 1;
                        updateTotalPrice();
                    }
                }

                // Add event listeners for the quantity buttons
                document.querySelector('button[onclick="increaseQuantity()"]').addEventListener('click', increaseQuantity);
                document.querySelector('button[onclick="decreaseQuantity()"]').addEventListener('click', decreaseQuantity);

                // Variant selection event listener (for radio buttons)
                const variantRadios = document.querySelectorAll('.variant-radio');

                variantRadios.forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        const selectedVariantId = radio.getAttribute('data-variant-id');
                        const selectedVariantPrice = radio.getAttribute('data-price');

                        // Update the hidden input with selected variant ID
                        document.getElementById('selected-variant-id').value = selectedVariantId;
                        document.getElementById('selected-variant-id-buy-now').value = selectedVariantId;

                        // Update total price
                        updateTotalPrice();
                    });
                });

                // Function to update the total price based on selected variant and quantity
                function updateTotalPrice() {
                    let selectedVariantPrice = 0;
                    const quantity = parseInt(quantityInput.value);

                    const selectedVariantId = document.getElementById('selected-variant-id').value;
                    if (selectedVariantId) {
                        selectedVariantPrice = parseFloat(document.querySelector(`input[data-variant-id="${selectedVariantId}"]`).getAttribute('data-price'));
                    }

                    const basePrice = <?php echo $product['price']; ?>; // Base product price from PHP
                    const totalPrice = (basePrice + selectedVariantPrice) * quantity;

                    // Update total price on page
                    document.getElementById('totalPrice').innerText = totalPrice.toFixed(2);
                }

                // Ensure total price is updated on page load
                updateTotalPrice();
            });
        </script>
</body>

</html>