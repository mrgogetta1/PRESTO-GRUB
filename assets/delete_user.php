<?php
session_start();
// Include database connection
include '../connection/connection.php';

// Check if the user is not logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page
    exit; // Prevent further execution of the script
}

// Check if the user ID is provided in the URL
if (!isset($_GET['id'])) {
    header("Location: users.php"); // Redirect to users page if ID is not provided
    exit; // Prevent further execution of the script
}

// Retrieve user ID from the URL
$user_id = $_GET['id'];

// Fetch user information from the database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 0) {
    echo "User not found";
    exit;
}

// Get user data
$user = $result->fetch_assoc();

// Close the statement
$stmt->close();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Delete user information from the database
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "User deleted successfully.";
        header("Location: ../users.php"); // Redirect to users page after deletion
        exit;
    } else {
        echo "Error deleting user: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h2>Delete User</h2>
    <p>Are you sure you want to delete the user '<?php echo $user['first_name'] . ' ' . $user['last_name']; ?>'?</p>
    <form method="POST" action="">
        <button type="submit" class="btn btn-danger">Delete</button>
        <a href="../users.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
