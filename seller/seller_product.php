<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: ../login.php");
    exit;
}

// Fetch all products for the logged-in seller
$user_id = $_SESSION['user_id'];
$productQuery = $conn->prepare("SELECT * FROM products WHERE user_id = ?");
$productQuery->bind_param("i", $user_id);
$productQuery->execute();
$productResult = $productQuery->get_result();

// Check if there are products
$noProductsMessage = ($productResult->num_rows === 0) ? "No products found." : "";

// Fetch all stores for the logged-in user
$storeQuery = $conn->prepare("SELECT * FROM stores WHERE user_id = ?");
$storeQuery->bind_param("i", $user_id);
$storeQuery->execute();
$storeResult = $storeQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Seller Product Panel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .menu {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-around;
            padding: 10px;
        }
        .content {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Presto Grub Seller Product Panel</h1>
        <form method="POST" action="../function/logout.php">
            <button type="submit" class="btn btn-primary">Logout</button>
        </form>
    </div>
    <div class="menu">
        <a href="seller.php" onclick="showDashboard()">Dashboard</a>
        <a href="sellerstore.php" onclick="showStores()">Stores</a>
        <a href="seller_product.php" onclick="showProducts()">Products</a>
        <a href="seller_order.php" onclick="showOrders()">Orders</a>
        <a href="seller_report.php" onclick="showCompleteOrders()">Report</a>
        <a href="seller_msg.php" onclick="showChat()">Chat</a>
    </div>

    <!-- Add Product Button -->
    <div class="text-center mb-4">
        <button class="btn btn-success" onclick="openAddModal()">Add Product</button>
    </div>

    <!-- Product List -->
    <h2 class="text-center mt-4">Your Products</h2>

    <div class="row">
        <?php if (!empty($noProductsMessage)): ?>
            <div class="col-12 text-center">
                <p><?php echo $noProductsMessage; ?></p>
            </div>
        <?php else: ?>
            <?php while ($product = $productResult->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
    <div class="card position-relative">
        <!-- X Button for Deleting Product -->
        <button class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px;" 
                onclick="confirmDeleteProduct(<?php echo $product['product_id']; ?>)">
            &times;
        </button>

        <!-- Product Image -->
        <img src="http://localhost/phpprogram/4/productimg/<?php echo htmlspecialchars($product['image']); ?>" 
             alt="Product Image" class="card-img-top img-fluid" style="max-height: 200px; object-fit: cover;">

        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
            <p class="card-text"><strong>Price:</strong> ₱<?php echo htmlspecialchars($product['price']); ?></p>

            <!-- Stock Status Dropdown -->
            <div class="form-group">
                <label for="stock_status_<?php echo $product['product_id']; ?>">Stock Status</label>
                <p class="card-text"><strong>Available Stocks:</strong> <?php echo htmlspecialchars($product['stock_quantity']); ?></p>
                <select class="form-control" id="stock_status_<?php echo $product['product_id']; ?>">
                    <option value="0" <?php echo $product['out_of_stock'] == 0 ? 'selected' : ''; ?>>Available</option>
                    <option value="1" <?php echo $product['out_of_stock'] == 1 ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>

            <!-- Variants List -->
            <div class="form-group">
                <label for="product_variants_<?php echo $product['product_id']; ?>">Product Variants</label>
                <ul class="list-group">
    <?php
    $variantQuery = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ?");
    $variantQuery->bind_param("i", $product['product_id']);
    $variantQuery->execute();
    $variantResult = $variantQuery->get_result();

    while ($variant = $variantResult->fetch_assoc()) {
        echo "<li class='list-group-item'>"
            . htmlspecialchars($variant['variant_name']) . " - SKU: "
            . htmlspecialchars($variant['sku']) . " - Price: $"
            . number_format($variant['price'], 2) . " - Stock: "
            . htmlspecialchars($variant['stock_quantity']) . "</li>";
    }

    $variantQuery->close();
    ?>
</ul>


                <button class="btn btn-secondary" onclick="openVariantModal(<?php echo $product['product_id']; ?>)">Add Variant</button>
            </div>

<!-- Add Variant Modal -->
<div class="modal fade" id="addVariantModal" tabindex="-1" role="dialog" aria-labelledby="addVariantModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addVariantForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVariantModalLabel">Add Product Variant</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="variant_product_id" name="product_id">

                    <!-- Dropdown or Add New Variant Section -->
                    <div class="form-group" id="existingVariantSection">
                        <label for="variant_name">Variant Name</label>
                        <select class="form-control" id="variant_name" name="variant_name">
                            <!-- Options dynamically populated via AJAX -->
                        </select>
                        <button type="button" class="btn btn-link" id="addNewVariantButton">Add New Variant Name</button>
                    </div>

                    <!-- Add New Variant Section -->
                    <div class="form-group d-none" id="newVariantSection">
                        <label for="new_variant_name">New Variant Name</label>
                        <input type="text" class="form-control" id="new_variant_name" placeholder="Enter new variant name">
                        <button type="button" class="btn btn-link" id="cancelNewVariantButton">Cancel</button>
                    </div>

                    <!-- Variant Details -->
                    <div id="variantDetails">
                        <!-- Input for SKU -->
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" required>
                        </div>

                        <!-- Input for Price -->
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>

                        <!-- Input for Stock Quantity -->
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                        </div>
                    </div>

                    <!-- Add Multiple Variants -->
                    <div class="form-group">
                        <button type="button" class="btn btn-info" id="addAnotherVariant">Add Another Variant</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Variants</button>
                </div>
            </form>
        </div>
    </div>
</div>



            <!-- Edit and Apply Buttons -->
            <div class="d-flex justify-content-between">
                <button class="btn btn-primary" onclick="openEditModal(<?php echo $product['product_id']; ?>, 
                        '<?php echo addslashes($product['name']); ?>', 
                        '<?php echo addslashes($product['description']); ?>', 
                        <?php echo htmlspecialchars($product['price']); ?>, 
                        <?php echo htmlspecialchars($product['stock_quantity']); ?>, 
                        '<?php echo htmlspecialchars($product['image']); ?>')">Edit Product</button>
                <button class="btn btn-info" onclick="applyStockStatus(<?php echo $product['product_id']; ?>)">Apply</button>
            </div>
        </div>
    </div>
</div>

            <?php endwhile; ?>
        <?php endif; ?>
    </div>


    <!-- Modal for Adding Product -->
<!-- Modal for Adding Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeAddModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" enctype="multipart/form-data" method="POST" action="../function/add_product.php">
                    <div class="form-group">
                        <label for="store_id">Select Store</label>
                        <select class="form-control" name="store_id" required>
                            <option value="">Choose a store...</option>
                            <?php while ($store = $storeResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($store['store_id']); ?>"><?php echo htmlspecialchars($store['store_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_product_name">Product Name</label>
                        <input type="text" class="form-control" id="add_product_name" name="add_product_name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_product_description">Product Description</label>
                        <textarea class="form-control" id="add_product_description" name="add_product_description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add_product_price">Price</label>
                        <input type="number" class="form-control" id="add_product_price" name="add_product_price" required>
                    </div>
                    <div class="form-group">
                        <label for="add_product_image">Product Image</label>
                        <input type="file" class="form-control" id="add_product_image" name="add_product_image" required>
                    </div>
                    <!-- New Category Field -->
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select class="form-control" name="category" required>
                            <option value="">Choose a category...</option>
                            <option value="Meals">Meals</option>
                            <option value="Desserts">Desserts</option>
                            <option value="Beverages">Beverages</option>
                            <option value="Snacks">Snacks</option>
                            <option value="Pasta">Pasta</option>
                        </select>
                    </div>
                    
                    <!-- Add Product Button -->
                    <div class="text-center mb-4">
                        <button type="submit" class="btn btn-success">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Modal for Editing Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeEditModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" enctype="multipart/form-data" method="POST" action="../function/edit_product.php">
                    <input type="hidden" name="product_id" id="edit_product_id">

                    <!-- Store Selection Dropdown -->
                    <div class="form-group">
                        <label for="edit_store_id">Select Store</label>
                        <select class="form-control" name="store_id" id="edit_store_id" required>
                            <option value="">Choose a store...</option>
                            <?php 
                            // Reset the store query result before reusing it in the edit modal
                            $storeQuery->execute();
                            $storeResult = $storeQuery->get_result();
                            while ($store = $storeResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($store['store_id']); ?>"><?php echo htmlspecialchars($store['store_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_product_name">Product Name</label>
                        <input type="text" class="form-control" id="edit_product_name" name="edit_product_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_description">Product Description</label>
                        <textarea class="form-control" id="edit_product_description" name="edit_product_description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_price">Price</label>
                        <input type="number" class="form-control" id="edit_product_price" name="edit_product_price" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_stock_quantity">Stock Quantity</label>
                        <input type="number" class="form-control" id="edit_stock_quantity" name="edit_stock_quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_image">Product Image</label>
                        <input type="file" class="form-control" id="edit_product_image" name="edit_product_image">
                    </div>
                    
                    <!-- Edit Product Button -->
                    <div class="text-center mb-4">
                        <button type="submit" class="btn btn-success">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    function openAddModal() {
        $('#addProductModal').modal('show');
    }

    function closeAddModal() {
        $('#addProductModal').modal('hide');
    }

    function openEditModal(productId, productName, productDescription, productPrice, productStock_quantity, productImage) {
        $('#edit_product_id').val(productId);
        $('#edit_product_name').val(productName);
        $('#edit_product_description').val(productDescription);
        $('#edit_product_price').val(productPrice);
        $('#edit_stock_quantity').val(productStock_quantity);
        $('#editProductModal').modal('show');
    }

    function closeEditModal() {
        $('#editProductModal').modal('hide');
    }

    function confirmDeleteProduct(productId) {
        if (confirm("Are you sure you want to delete this product?")) {
            window.location.href = '../function/delete_product.php?id=' + productId;
        }
    }

    function applyStockStatus(productId) {
        var stockStatus = document.getElementById('stock_status_' + productId).value;

        // AJAX request to toggle stock status
        $.ajax({
            url: '../function/toggle_stock_status.php',
            method: 'POST',
            data: { product_id: productId, status: stockStatus },
            success: function(response) {
                const res = JSON.parse(response); // Parse JSON response
                alert(res.message); // Show success message
                if (res.status === 'success') {
                    location.reload(); // Refresh to see the updated stock status
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('Failed to update stock status. Please try again.');
            }
        });
    }

    







    function openVariantModal(productId) {
    // Set product ID
    $('#variant_product_id').val(productId);

    // Fetch existing variants for the product
    $.ajax({
        url: '../function/get_variants.php', // Backend to fetch variants
        method: 'GET',
        data: { product_id: productId },
        success: function (response) {
            console.log('Raw response from get_variants.php:', response); // Debug log
            if (typeof response === 'string') {
                try {
                    const res = JSON.parse(response);
                    console.log('Parsed response:', res); // Debug log

                    if (res.status === 'success' && res.variants.length > 0) {
                        // Populate dropdown with existing variants
                        $('#variant_name').empty();
                        res.variants.forEach(variant => {
                            $('#variant_name').append(`<option value="${variant.variant_name}">${variant.variant_name}</option>`);
                        });

                        $('#existingVariantSection').removeClass('d-none');
                        $('#newVariantSection').addClass('d-none');
                    } else {
                        // Show add new variant field
                        $('#existingVariantSection').addClass('d-none');
                        $('#newVariantSection').removeClass('d-none');
                    }

                    $('#addVariantModal').modal('show');
                } catch (error) {
                    console.error('Error parsing response:', error);
                    alert('Failed to load variant data. Please check the server output.');
                }
            } else {
                console.warn('Received an unexpected response type:', typeof response);
                alert('Unexpected response type. Please check the server response.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error in get_variants.php:', error);
            alert('Failed to fetch variants. Please try again.');
        }
    });
}

// Toggle between adding a new variant and selecting an existing one
$('#addNewVariantButton').on('click', function () {
    $('#existingVariantSection').addClass('d-none');
    $('#newVariantSection').removeClass('d-none');
});

$('#cancelNewVariantButton').on('click', function () {
    $('#newVariantSection').addClass('d-none');
    $('#existingVariantSection').removeClass('d-none');
});

// Add multiple variants functionality
$('#addAnotherVariant').on('click', function () {
    const clone = $('#variantDetails').clone().removeAttr('id').find('input').val('').end();
    clone.addClass('cloned'); // Add a class to identify dynamically added variants
    $('.modal-body').append(clone);
});

// Handle form submission
$('#addVariantForm').on('submit', function (e) {
    e.preventDefault();

    let formData = $(this).serialize();
    console.log('Form data being sent:', formData); // Debug log

    // Check if adding a new variant and append the new variant name
    if (!$('#newVariantSection').hasClass('d-none')) {
        const newVariantName = $('#new_variant_name').val().trim();
        if (newVariantName === '') {
            alert('Please enter a new variant name.');
            return;
        }
        formData += `&variant_name=${encodeURIComponent(newVariantName)}`;
    }

    // Send data to the backend
    $.ajax({
        url: '../function/seller_variant.php', // Backend to handle variant addition
        method: 'POST',
        data: formData,
        success: function (response) {
            console.log('Raw response from seller_variant.php:', response); // Debug log
            if (typeof response === 'string') {
                try {
                    const res = JSON.parse(response);
                    console.log('Parsed response:', res); // Debug log

                    if (res.status === 'success') {
                        alert(res.message);

                        // Add the new variant to the dropdown if it was a new variant submission
                        if (!$('#newVariantSection').hasClass('d-none')) {
                            const newVariantName = $('#new_variant_name').val().trim();
                            $('#variant_name').append(`<option value="${encodeURIComponent(newVariantName)}">${newVariantName}</option>`);
                            $('#variant_name').val(newVariantName); // Select the newly added variant
                        }

                        $('#addVariantModal').modal('hide');
                        // Optionally, refresh the modal data without a full page reload
                        openVariantModal($('#variant_product_id').val());
                    } else {
                        console.warn('Error from server:', res.message);
                        alert(res.message);
                    }
                } catch (error) {
                    console.error('Error parsing response:', error);
                    alert('Failed to process response. Please check the server output.');
                }
            } else {
                console.warn('Received an unexpected response type:', typeof response);
                alert('Unexpected response type. Please check the server response.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error in seller_variant.php:', error);
            alert('Failed to add variant. Please try again.');
        }
    });
});

// Utility function to clear modal inputs
function clearVariantModal() {
    $('#variant_product_id').val('');
    $('#new_variant_name').val('');
    $('#variant_name').empty();
    $('#newVariantSection').addClass('d-none');
    $('#existingVariantSection').removeClass('d-none');
    $('#addVariantModal input').val(''); // Clear all input fields

    // Remove dynamically added fields
    $('.modal-body .cloned').remove();
}

// Reset the modal when it's closed
$('#addVariantModal').on('hidden.bs.modal', function () {
    clearVariantModal();
});


</script>
</body>
</html>
