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

// Fetch variants for each product
$variants_query = "SELECT * FROM product_variants WHERE product_id = ?";
$variant_stmt = $conn->prepare($variants_query);

$search_query = isset($_GET['search']) ? $_GET['search'] : '';

function removeItem($conn, $cart_id)
{
    $deleteStmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
    $deleteStmt->bind_param("i", $cart_id);
    return $deleteStmt->execute();
}

function updateItemQuantity($conn, $cart_id, $quantity)
{
    $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $updateStmt->bind_param("ii", $quantity, $cart_id);
    return $updateStmt->execute();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_quantity'])) {
        $cart_id = intval($_POST['update_quantity']);
        $quantity = intval($_POST['quantity_' . $cart_id]);
        updateItemQuantity($conn, $cart_id, $quantity);
    }

    if (isset($_POST['delete_item'])) {
        $cart_id = intval($_POST['delete_item']);
        removeItem($conn, $cart_id);
    }

    if (isset($_POST['proceed_checkout'])) {
        $conn->begin_transaction();
        try {
            $cartQuery = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
            $cartQuery->bind_param("i", $user_id);
            $cartQuery->execute();
            $cartItems = $cartQuery->get_result();

            if ($cartItems->num_rows > 0) {
                // Pass cart data via session or temporary storage
                $_SESSION['cart_items'] = $cartItems->fetch_all(MYSQLI_ASSOC);
                header("Location: delivery_form.php");
                exit;
            } else {
                throw new Exception("Cart is empty.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }
}

// Fetch updated cart items with product variants
$query = "SELECT products.*, cart.quantity, cart.payment_method, cart.cart_id 
                    FROM cart 
                    JOIN products ON products.product_id = cart.product_id 
                    WHERE cart.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;
$num_items = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Presto Grub - Order</title>
    <link rel="stylesheet" type="text/css" href="css/order.css">
    <link rel="stylesheet" type="text/css" href="css/header.css">

    <style>
        /* Hide the sidebar and show the toggle button when screen width <= 400px */
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

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

        <div class="container">
            <h2>Order Cart</h2>
            <div class="cart">
                <div class="products">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="order-form">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="product" data-price="<?php echo htmlspecialchars($row['price']); ?>" data-cart-id="<?php echo $row['cart_id']; ?>">
                                <img src="productimg/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" onerror="this.onerror=null; this.src='images/placeholder.png';">

                                <div class="product-info">
                                    <h2 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h2>
                                    <p>₱<span class="price"><?php echo htmlspecialchars($row['price']); ?></span></p>

                                    <!-- Dropdown to select variants -->
                                    <label for="variant">Select Variant:</label>
                                    <select name="variant_id_<?php echo $row['cart_id']; ?>">
                                        <?php
                                        $product_id = $row['product_id'];
                                        $variant_stmt->bind_param("i", $product_id);
                                        $variant_stmt->execute();
                                        $variant_result = $variant_stmt->get_result();

                                        while ($variant = $variant_result->fetch_assoc()) {
                                            echo "<option value='{$variant['variant_id']}'>
                            SKU: {$variant['sku']} - ₱" . number_format($variant['price'], 2) .
                                                "</option>";
                                        }
                                        ?>
                                    </select>

                                    <label for="quantity">Quantity:</label>
                                    <input type="number" name="quantity_<?php echo htmlspecialchars($row['cart_id']); ?>" value="<?php echo htmlspecialchars($row['quantity']); ?>" min="1" required>

                                    <button type="submit" name="delete_item" value="<?php echo htmlspecialchars($row['cart_id']); ?>" class="product-remove">Remove</button>
                                </div>
                            </div>

                            <?php
                            $total_price += $row['price'] * $row['quantity'];
                            $num_items += $row['quantity'];
                            ?>
                        <?php endwhile; ?>

                        <div class="cart-total">
                            <div class="cart-summary">
                                <p class="total-price">Total Price: ₱ <span id="total-price"><?php echo htmlspecialchars($total_price); ?></span></p>
                                <p class="total-quan">Total Quantity: <span id="total-quantity"><?php echo htmlspecialchars($num_items); ?></span></p>
                            </div>
                            <div class="checkout-action">
                                <?php if ($num_items > 0): ?>
                                    <button id="checkoutBtn" name="proceed_checkout" class="checkout-btn">Proceed to Checkout</button>
                                <?php else: ?>
                                    <p class="empty-cart">No items in cart to checkout.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Footer -->
        <?php include 'footer.php'; ?>
    </div>
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
            // Select all quantity input fields
            const quantityInputs = document.querySelectorAll('input[type="number"]');

            // Update total price and total quantity when the quantity changes
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Get the price and quantity of the product whose quantity was changed
                    const productElement = input.closest('.product');
                    const price = parseFloat(productElement.dataset.price); // Price of the product
                    const quantity = parseInt(input.value); // New quantity entered by the user

                    // Recalculate total price and total quantity
                    let totalPrice = 0;
                    let totalQuantity = 0;

                    // Loop through each product and recalculate total price and quantity
                    document.querySelectorAll('.product').forEach(product => {
                        const productPrice = parseFloat(product.dataset.price);
                        const productQuantity = parseInt(product.querySelector('input[type="number"]').value);

                        totalPrice += productPrice * productQuantity;
                        totalQuantity += productQuantity;
                    });

                    // Update the total price and total quantity in the DOM
                    document.getElementById('total-price').innerText = totalPrice.toFixed(2); // Update total price
                    document.getElementById('total-quantity').innerText = totalQuantity; // Update total quantity
                });
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
    </script>
</body>

</html>