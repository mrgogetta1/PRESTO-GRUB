<?php
session_start();
require_once '../connection/connection.php';

// Check if the user is not logged in or is not a seller or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['isAdmin'] != 1 && $_SESSION['isAdmin'] != 2)) {
    header("Location: ../login.php");
    exit;
}

// Fetch all store information for the logged-in user
$user_id = $_SESSION['user_id'];
$storeQuery = $conn->prepare("SELECT * FROM stores WHERE user_id = ?");
$storeQuery->bind_param("i", $user_id);
$storeQuery->execute();
$storesResult = $storeQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presto Grub Seller Panel</title>
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
        <h1>Presto Grub Seller Panel</h1>
        <!-- Logout button -->
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

    <h2 class="text-center mt-4">Your Store Information</h2>

    <div class="text-center mb-4">
        <button class="btn btn-success" data-toggle="modal" data-target="#addStoreModal">Add Store</button>
    </div>

    <div class="row">
        <?php while ($store = $storesResult->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="http://localhost/phpprogram/2/store_images/<?php echo htmlspecialchars($store['store_image']); ?>" alt="Store Image" class="card-img-top img-fluid" style="max-height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($store['store_name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($store['store_description']); ?></p>
                        <p class="card-text"><strong>Contact:</strong> <?php echo htmlspecialchars($store['store_contact']); ?></p>
                        <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($store['store_location']); ?></p>
                        <button class="btn btn-primary" onclick="openEditModal(<?php echo $store['store_id']; ?>, '<?php echo addslashes($store['store_name']); ?>', '<?php echo addslashes($store['store_description']); ?>', '<?php echo addslashes($store['store_contact']); ?>', '<?php echo addslashes($store['store_location']); ?>', '<?php echo htmlspecialchars($store['store_image']); ?>')">Edit Store</button>
                        <button class="btn btn-danger" onclick="deleteStore(<?php echo $store['store_id']; ?>)">Delete Store</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal for Adding Store -->
<div class="modal fade" id="addStoreModal" tabindex="-1" aria-labelledby="addStoreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStoreModalLabel">Add New Store</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addStoreForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="new_store_name">Store Name</label>
                        <input type="text" class="form-control" id="new_store_name" name="store_name" required>
                    </div>
                    <div class="form-group">
                        <label for="new_store_description">Store Description</label>
                        <textarea class="form-control" id="new_store_description" name="store_description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="new_store_contact">Contact</label>
                        <input type="text" class="form-control" id="new_store_contact" name="store_contact" required>
                    </div>
                    <div class="form-group">
                        <label for="new_store_location">Location</label>
                        <input type="text" class="form-control" id="new_store_location" name="store_location" required>
                    </div>
                    <div class="form-group">
                        <label for="new_store_image">Store Image</label>
                        <input type="file" class="form-control" id="new_store_image" name="store_image" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Store</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Store -->
<div class="modal fade" id="editStoreModal" tabindex="-1" aria-labelledby="editStoreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStoreModalLabel">Edit Store Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editStoreForm" enctype="multipart/form-data">
                    <input type="hidden" id="store_id" name="store_id">
                    <div class="form-group">
                        <label for="store_name">Store Name</label>
                        <input type="text" class="form-control" id="store_name" name="store_name" required>
                    </div>
                    <div class="form-group">
                        <label for="store_description">Store Description</label>
                        <textarea class="form-control" id="store_description" name="store_description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="store_contact">Contact</label>
                        <input type="text" class="form-control" id="store_contact" name="store_contact" required>
                    </div>
                    <div class="form-group">
                        <label for="store_location">Location</label>
                        <input type="text" class="form-control" id="store_location" name="store_location" required>
                    </div>
                    <div class="form-group">
                        <label for="store_image">Store Image</label>
                        <input type="file" class="form-control" id="store_image" name="store_image">
                        <small class="form-text text-muted">Leave empty to keep the current image.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        // Add store
        $('#addStoreForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            const formData = new FormData(this);
            $.ajax({
                url: '../function/add_store.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert(response);
                    location.reload(); // Reload the page to see the new store
                },
                error: function(xhr, status, error) {
                    alert("Error: " + error);
                }
            });
        });

        // Edit store
        $('#editStoreForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            const formData = new FormData(this);
            $.ajax({
                url: '../function/edit_store.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert(response);
                    location.reload(); // Reload the page to see the updated store
                },
                error: function(xhr, status, error) {
                    alert("Error: " + error);
                }
            });
        });
    });

    function openEditModal(id, name, description, contact, location, image) {
        $('#store_id').val(id);
        $('#store_name').val(name);
        $('#store_description').val(description);
        $('#store_contact').val(contact);
        $('#store_location').val(location);
        $('#editStoreModal').modal('show');
    }

    function deleteStore(storeId) {
        if (confirm("Are you sure you want to delete this store?")) {
            $.ajax({
                url: 'delete_store.php', // PHP script that will handle deletion
                type: 'POST',
                data: { store_id: storeId },
                success: function(response) {
                    alert(response); // Show a message returned by the PHP script
                    location.reload(); // Reload the page to reflect the changes
                },
                error: function(xhr, status, error) {
                    alert("Error: " + error);
                }
            });
        }
    }

</script>

</body>
</html>
