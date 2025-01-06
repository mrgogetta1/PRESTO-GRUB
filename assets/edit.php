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
    // Retrieve updated form data
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : $user['first_name'];
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : $user['last_name'];
    $email = isset($_POST['email']) ? $_POST['email'] : $user['email'];
    // Add validation and sanitization as needed

    // Update user information in the database
    $sql = "UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $first_name, $last_name, $email, $user_id);
    if ($stmt->execute()) {
        header("Location: ../users.php");
    } else {
        echo "Error updating user information: " . $stmt->error;
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
    <title>Edit User Information</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h2>Edit User Information</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
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
