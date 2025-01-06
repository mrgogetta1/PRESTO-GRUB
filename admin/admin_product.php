<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 2) {
    header("Location: login.php"); // Redirect to login page
    exit; // Prevent further execution of the script
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Admin Panel - Products</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .menu {
            background-color: #f8f9fa;
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
            background-color: #e9ecef;
            border-radius: 5px;
        }

        .content {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #343a40;
            color: #fff;
        }

        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Presto Grub Admin Panel - Products</h1>

        <!-- Logout button -->
        <form method="POST" action="../function/logout.php">
            <button type="submit" class="btn btn-primary">Logout</button>
        </form>
    </div>
    <div class="menu">
        <a href="admin_panel.php" onclick="showDashboard()">Dashboard</a>
        <a href="admin_store.php" onclick="showStores()">Stores</a>
        <a href="admin_product.php" onclick="showProducts()">Products</a>
        <a href="order_admin.php" onclick="showOrders()">Orders</a>
        <a href="admin_order_complete.php" onclick="showCompleteOrders()">Complete Orders</a>
        <a href="admin_category.php" onclick="showUsers()">Categories</a>
        <a href="users.php" onclick="showUsers()">Users</a>
    </div>
    
    

    <div class="content">
        <h2>View Products</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Owner Name</th>
                    <th>Store Name</th>
                    <th>Email</th>
                    <th>Most Sold</th>
                    <th>Category ID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.product_id, p.name AS product_name, CONCAT(u.first_name, ' ', u.last_name) AS owner_name, 
                        s.store_name, u.email, p.category_id,
                        (SELECT COUNT(*) FROM orders o WHERE o.product_id = p.product_id AND o.status = 'complete') AS most_sold
                        FROM products p
                        INNER JOIN stores s ON p.store_id = s.store_id
                        INNER JOIN users u ON s.user_id = u.id";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["product_id"] . "</td>";
                        echo "<td>" . $row["product_name"] . "</td>";
                        echo "<td>" . $row["owner_name"] . "</td>";
                        echo "<td>" . $row["store_name"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>" . $row["most_sold"] . "</td>";
                        echo "<td>" . $row["category_id"] . "</td>";
                        echo "<td>
                                <button class='btn btn-info edit-btn' data-id='" . $row["product_id"] . "' 
                                        data-name='" . $row["product_name"] . "' 
                                        data-owner='" . $row["owner_name"] . "' 
                                        data-store='" . $row["store_name"] . "' 
                                        data-email='" . $row["email"] . "'>Edit</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No products found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap Modal for Edit -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editProductForm" method="POST" action="edit_product.php">
            <input type="hidden" id="productId" name="productId">
            <div class="form-group">
                <label for="productName">Product Name</label>
                <input type="text" id="productName" name="productName" class="form-control" placeholder="Product Name">
            </div>
            <div class="form-group">
                <label for="storeName">Store Name</label>
                <input type="text" id="storeName" name="storeName" class="form-control" placeholder="Store Name">
            </div>
            <div class="form-group">
                <label for="ownerName">Owner Name</label>
                <input type="text" id="ownerName" name="ownerName" class="form-control" placeholder="Owner Name">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Email">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- JavaScript for Edit Popup -->
<script>
    // Add event listener to all edit buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Get product details from the data attributes
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            const ownerName = button.getAttribute('data-owner');
            const storeName = button.getAttribute('data-store');
            const email = button.getAttribute('data-email');
            
            // Populate modal fields
            document.getElementById('productId').value = productId;
            document.getElementById('productName').value = productName;
            document.getElementById('storeName').value = storeName;
            document.getElementById('ownerName').value = ownerName;
            document.getElementById('email').value = email;
            
            // Show the modal
            $('#editProductModal').modal('show');
        });
    });
</script>

</body>
</html>
