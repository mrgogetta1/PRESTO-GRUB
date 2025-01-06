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
    <title>Presto Grub Admin Panel - Stores</title>
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
        <h1>Presto Grub Admin Panel - Stores</h1>

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
    <h2>View Stores</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Store Name</th>
                <th>Owner Name</th>
                <th>Email</th>
                <th>Contact No.</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and display all stores with associated user information
            $sql = "SELECT s.store_id, s.store_name, s.store_description, s.store_location, 
                        u.first_name, u.last_name, u.email, u.contact_no, u.address 
                    FROM stores s
                    INNER JOIN users u ON s.user_id = u.id
                    WHERE u.isAdmin = 1"; // Only show stores where user is a seller (isAdmin = 1)
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["store_id"] . "</td>";
                    echo "<td>" . $row["store_name"] . "</td>";
                    echo "<td>" . $row["first_name"] . " " . $row["last_name"] . "</td>";
                    echo "<td>" . $row["email"] . "</td>";
                    echo "<td>" . $row["contact_no"] . "</td>";
                    echo "<td>" . $row["address"] . "</td>";
                    echo "<td>
                            <button class='btn btn-info edit-btn' data-id='" . $row["store_id"] . "' 
                                    data-name='" . $row["store_name"] . "' 
                                    data-description='" . $row["store_description"] . "' 
                                    data-location='" . $row["store_location"] . "' 
                                    data-contact='" . $row["contact_no"] . "' 
                                    data-address='" . $row["address"] . "'>Edit</button>
                            <button class='btn btn-danger delete-btn' data-id='" . $row["store_id"] . "'>Delete</button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No stores found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Edit Form Popup -->
<div id="editFormPopup" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Store</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editStoreForm" method="POST" action="../function/edit_store.php">
                    <!-- Fields for editing store information -->
                    <input type="hidden" id="storeId" name="store_id"> <!-- Hidden field for store_id -->
                    <div class="form-group">
                        <label for="storeName">Store Name</label>
                        <input type="text" class="form-control" id="storeName" name="store_name" placeholder="Store Name" required>
                    </div>
                    <div class="form-group">
                        <label for="storeContact">Store Contact</label>
                        <input type="text" class="form-control" id="storeContact" name="store_contact" placeholder="Store Contact" required>
                    </div>
                    <div class="form-group">
                        <label for="storeDescription">Store Description</label>
                        <textarea class="form-control" id="storeDescription" name="store_description" placeholder="Store Description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="storeLocation">Store Location</label>
                        <input type="text" class="form-control" id="storeLocation" name="store_location" placeholder="Store Location" required>
                    </div>
                    <div class="form-group">
                        <label for="storeAddress">Store Address</label>
                        <input type="text" class="form-control" id="storeAddress" name="store_address" placeholder="Store Address" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Add event listener to all edit buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Get the store information from the data attributes
            const storeId = button.getAttribute('data-id');
            const storeName = button.getAttribute('data-name');
            const storeDescription = button.getAttribute('data-description');
            const storeLocation = button.getAttribute('data-location');
            const storeContact = button.getAttribute('data-contact');
            const storeAddress = button.getAttribute('data-address');

            // Populate the modal form fields
            document.getElementById('storeId').value = storeId;
            document.getElementById('storeName').value = storeName;
            document.getElementById('storeDescription').value = storeDescription;
            document.getElementById('storeLocation').value = storeLocation;
            document.getElementById('storeContact').value = storeContact;
            document.getElementById('storeAddress').value = storeAddress;

            // Show the popup form for editing
            $('#editFormPopup').modal('show');
        });
    });

    // Add event listener to all delete buttons
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const storeId = button.getAttribute('data-id');
            const confirmation = confirm('Are you sure you want to delete this store?');

            if (confirmation) {
                // Redirect to the delete_store.php with the store_id parameter
                window.location.href = '../function/delete_store.php?store_id=' + storeId;
            }
        });
    });
</script>



</body>
</html>
