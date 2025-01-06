<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 2) {
    header("Location: ./login.php");
    exit;
}

// Fetch categories from the database
$categoriesResult = $conn->query("SELECT * FROM categories");

// Fetch products that do not have a category
$productsResult = $conn->query("SELECT * FROM products WHERE category_id IS NULL");

// Handle adding a category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $categoryName = $conn->real_escape_string($_POST['category_name']);
    $conn->query("INSERT INTO categories (category_name) VALUES ('$categoryName')");
    header("Location: admin_category.php");
    exit;
}

// Handle editing a category
if (isset($_GET['edit'])) {
    $categoryId = $_GET['edit'];
    $categoryResult = $conn->query("SELECT * FROM categories WHERE category_id = $categoryId");
    $categoryData = $categoryResult->fetch_assoc();
}

// Handle updating a category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $categoryId = $_POST['category_id'];
    $categoryName = $conn->real_escape_string($_POST['category_name']);
    $conn->query("UPDATE categories SET category_name = '$categoryName' WHERE category_id = $categoryId");
    header("Location: admin_category.php");
    exit;
}

// Handle deleting a category
if (isset($_GET['delete'])) {
    $categoryId = $_GET['delete'];
    // Move all products in the category to "Products Without Category"
    $conn->query("UPDATE products SET category_id = NULL WHERE category_id = $categoryId");
    $conn->query("DELETE FROM categories WHERE category_id = $categoryId");
    header("Location: admin_category.php");
    exit;
}

// Handle deleting a product
if (isset($_GET['delete_product'])) {
    $productId = $_GET['delete_product'];
    $conn->query("DELETE FROM products WHERE product_id = $productId");
    header("Location: admin_category.php");
    exit;
}

// Handle assigning a category to a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_category'])) {
    $productId = $_POST['product_id'];
    $categoryId = $_POST['category_id'];
    $conn->query("UPDATE products SET category_id = '$categoryId' WHERE product_id = '$productId'");
    header("Location: admin_category.php");
    exit;
}

// Handle changing product's category
if (isset($_GET['move_product'])) {
    $productId = $_GET['move_product'];
    $newCategoryId = $_GET['category_id'];
    $conn->query("UPDATE products SET category_id = '$newCategoryId' WHERE product_id = '$productId'");
    header("Location: admin_category.php");
    exit;
}
// Fetch the category id if 'view' is clicked
if (isset($_GET['view'])) {
    $viewCategoryId = $_GET['view'];
    
    // Fetch the category details to display in the modal
    $categoryQuery = $conn->query("SELECT * FROM categories WHERE category_id = $viewCategoryId");
    $category = $categoryQuery->fetch_assoc();
    
    // Fetch products in that category
    $productsInCategory = $conn->query("SELECT * FROM products WHERE category_id = $viewCategoryId");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Admin Panel - Categories</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
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

        .menu a {
            color: #333;
            text-decoration: none;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }

        .menu a:hover {
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .content {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .content h2 {
            margin-top: 0;
        }

        .chart-container {
            width: 100%;
            height: 350px; /* Ensuring all charts are the same height */
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Presto Grub Admin Panel</h1>
        <form method="POST" action="../function/logout.php">
            <button type="submit" class="btn btn-primary">Logout</button>
        </form>
    </div>

    <!-- Menu -->
    <div class="menu">
        <a href="admin_panel.php" onclick="showDashboard()">Dashboard</a>
        <a href="admin_store.php" onclick="showStores()">Stores</a>
        <a href="admin_product.php" onclick="showProducts()">Products</a>
        <a href="order_admin.php" onclick="showOrders()">Orders</a>
        <a href="admin_order_complete.php" onclick="showCompleteOrders()">Complete Orders</a>
        <a href="admin_category.php" onclick="showUsers()">Categories</a>
        <a href="users.php" onclick="showUsers()">Users</a>
    </div>

    <!-- Categories Section -->
    <div class="content">
        <h2>Manage Categories</h2>

        <!-- Button to Open Add Category Modal -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addCategoryModal">Add Category</button>

       <!-- Categories Table -->
<table class="table table-bordered mt-4">
    <thead>
        <tr>
            <th>#</th>
            <th>Category Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($category = $categoriesResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo $category['category_id']; ?></td>
                <td><?php echo $category['category_name']; ?></td>
                <td>
                   
                    <!-- Edit Button -->
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editCategoryModal" 
                            onclick="document.getElementById('editCategoryId').value = <?php echo $category['category_id']; ?>; document.getElementById('editCategoryName').value = '<?php echo $category['category_name']; ?>';">
                        Edit
                    </button>
                    <!-- Delete Button -->
                    <a href="admin_category.php?delete=<?php echo $category['category_id']; ?>" class="btn btn-danger btn-sm" 
                       onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- View Category Modal -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1" role="dialog" aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCategoryModalLabel">View Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (isset($category)): ?>
                    <h5>Category: <?php echo $category['category_name']; ?></h5>
                    
                    <!-- Display Products in this Category -->
                    <h6>Products in this Category:</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $productsInCategory->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td>
                                        <!-- Change Category Button -->
                                        <a href="admin_category.php?move_product=<?php echo $product['product_id']; ?>&category_id=<?php echo $category['category_id']; ?>" class="btn btn-info btn-sm">Move to Another Category</a>
                                        
                                        <!-- Delete Product Button -->
                                        <a href="admin_category.php?delete_product=<?php echo $product['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No category found or invalid category ID.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    <!-- Products Without Category Section -->
    <div class="content">
        <h2>Products Without Category</h2>

        <!-- Products Table -->
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $productsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td><?php echo $product['name']; ?></td>
                        <td>
                            <!-- Assign Category Button -->
                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#assignCategoryModal" 
                                    onclick="document.getElementById('product_id').value = <?php echo $product['product_id']; ?>;">
                                Assign Category
                            </button>
                            <!-- Delete Product Button -->
                            <a href="admin_category.php?delete_product=<?php echo $product['product_id']; ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="admin_category.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" class="form-control" name="category_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_category">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="admin_category.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editCategoryId" name="category_id">
                    <div class="form-group">
                        <label for="editCategoryName">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="update_category">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Category Modal -->
<div class="modal fade" id="assignCategoryModal" tabindex="-1" role="dialog" aria-labelledby="assignCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="admin_category.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignCategoryModalLabel">Assign Category to Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="form-group">
                        <label for="category_id">Select Category</label>
                        <select class="form-control" name="category_id" required>
                            <?php 
                            // Reset categoriesResult for assign modal
                            $categoriesResult->data_seek(0);
                            while ($category = $categoriesResult->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="assign_category">Assign Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
