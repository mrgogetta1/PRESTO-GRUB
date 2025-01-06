<?php
session_start();
require_once 'connection/connection.php';

// Get the search term from the GET request
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';

// Search products
$product_query = "
    SELECT p.*, COUNT(o.product_id) AS order_count
    FROM products p
    LEFT JOIN orders o ON p.product_id = o.product_id AND o.status = 'complete'
    WHERE p.name LIKE ?
    GROUP BY p.name
    ORDER BY p.name
";

$stmt = $conn->prepare($product_query);
$stmt->bind_param("s", $search);
$stmt->execute();
$products = $stmt->get_result();

// Search stores
$store_query = "
    SELECT s.*, COALESCE(ush.views, 0) AS user_views
    FROM stores s
    LEFT JOIN user_store_history ush ON s.store_id = ush.store_id
    WHERE s.store_name LIKE ?
    ORDER BY user_views DESC
";

$stmt = $conn->prepare($store_query);
$stmt->bind_param("s", $search);
$stmt->execute();
$stores = $stmt->get_result();

// Output the search results as HTML
echo '<h3>Search Results:</h3>';

if ($products->num_rows > 0) {
    echo '<h4>Products</h4>';
    while ($product = $products->fetch_assoc()) {
        echo '<div class="search-result-item">
                <a href="view_product.php?product_id=' . htmlspecialchars($product['product_id']) . '">' . htmlspecialchars($product['name']) . '</a>
              </div>';
    }
} else {
    echo '<p>No products found.</p>';
}

if ($stores->num_rows > 0) {
    echo '<h4>Stores</h4>';
    while ($store = $stores->fetch_assoc()) {
        echo '<div class="search-result-item">
                <a href="view_store.php?store_id=' . htmlspecialchars($store['store_id']) . '">' . htmlspecialchars($store['store_name']) . '</a>
              </div>';
    }
} else {
    echo '<p>No stores found.</p>';
}
?>
