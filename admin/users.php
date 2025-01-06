<?php
session_start();
// Include database connection
include '../connection/connection.php';

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
    <title>Presto Grub Admin Panel - Users</title>
    <!-- Bootstrap CSS -->
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
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Presto Grub Admin Panel - Users</h1>
        <!-- Logout button -->
        <form method="POST" action="../function/logout.php">
            <button type="submit" class="btn btn-primary">Logout</button>
        </form>
    </div>

    <!-- Navigation Menu -->
    <div class="menu">
        <a href="admin_panel.php" onclick="showDashboard()">Dashboard</a>
        <a href="admin_store.php" onclick="showStores()">Stores</a>
        <a href="admin_product.php" onclick="showProducts()">Products</a>
        <a href="order_admin.php" onclick="showOrders()">Orders</a>
        <a href="admin_order_complete.php" onclick="showCompleteOrders()">Complete Orders</a>
        <a href="admin_category.php" onclick="showUsers()">Categories</a>
        <a href="users.php" onclick="showUsers()">Users</a>
    </div>

    <!-- CRUD Operations Section -->
    <div class="content">
        <!-- Read Users Table -->
        <h2>View Users</h2>
        <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch and display users from the database
                $sql = "SELECT isAdmin, first_name, last_name, email, user_address, contact_no, course, section, username FROM users";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Map isAdmin to role description
                        $role = "";
                        switch ($row["isAdmin"]) {
                            case 0:
                                $role = "Normal User";
                                break;
                            case 1:
                                $role = "Seller";
                                break;
                        }

                        echo "<tr>";
                        echo "<td>" . $role . "</td>";
                        echo "<td>" . $row["first_name"] . "</td>";
                        echo "<td>" . $row["last_name"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>
                                <button class='btn btn-info edit-btn' data-toggle='modal' data-target='#editUserModal'
                                data-role='" . $row["isAdmin"] . "'
                                data-first_name='" . $row["first_name"] . "'
                                data-last_name='" . $row["last_name"] . "'
                                data-email='" . $row["email"] . "'
                                data-user_address='" . $row["user_address"] . "'
                                data-contact_no='" . $row["contact_no"] . "'
                                data-course='" . $row["course"] . "'
                                data-section='" . $row["section"] . "'
                                data-username='" . $row["username"] . "'>Edit</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="user_edit.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit-user-id">
                    <div class="form-group">
                        <label for="edit-role">Role</label>
                        <select name="role" id="edit-role" class="form-control">
                            <option value="0">Normal User</option>
                            <option value="1">Seller</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-first-name">First Name</label>
                        <input type="text" name="first_name" id="edit-first-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-last-name">Last Name</label>
                        <input type="text" name="last_name" id="edit-last-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">Email</label>
                        <input type="email" name="email" id="edit-email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-user-address">Address</label>
                        <input type="text" name="user_address" id="edit-user-address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit-contact-no">Contact No</label>
                        <input type="text" name="contact_no" id="edit-contact-no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit-course">Course</label>
                        <input type="text" name="course" id="edit-course" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit-section">Section</label>
                        <input type="text" name="section" id="edit-section" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit-username">Username</label>
                        <input type="text" name="username" id="edit-username" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fill the edit modal with user data
    $('.edit-btn').click(function() {
        $('#edit-role').val($(this).data('role'));
        $('#edit-first-name').val($(this).data('first_name'));
        $('#edit-last-name').val($(this).data('last_name'));
        $('#edit-email').val($(this).data('email'));
        $('#edit-user-address').val($(this).data('user_address'));
        $('#edit-contact-no').val($(this).data('contact_no'));
        $('#edit-course').val($(this).data('course'));
        $('#edit-section').val($(this).data('section'));
        $('#edit-username').val($(this).data('username'));
    });
</script>

</body>
</html>
