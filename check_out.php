<?php
session_start();

if (isset($_POST['buy_now'])) {
    $product_id = $_POST['product_id'];

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add the product to the cart with quantity 1 if not already in the cart
    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = 1;
    }

    // Redirect to the checkout page
    header("Location: checkout_form.php");
    exit;
}
?>
<div class="product-item">
    <a href="view_product.php?product_id=<?php echo htmlspecialchars($product['product_id']); ?>">
        <img src="productimg/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
    </a>

    <!-- Add to Cart Button -->
    <form action="order.php" method="POST" style="display: inline-block;">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" min="1" value="1" style="width: 60px;">
        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
    </form>

    <!-- Buy Now Button -->
    <form action="checkout.php" method="POST" style="display: inline-block; margin-left: 10px;">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
        <input type="hidden" name="quantity" id="buy_now_quantity" value="1">
        <button type="submit" name="buy_now" class="btn btn-success">Buy Now</button>
    </form>
</div>
