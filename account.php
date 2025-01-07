<?php
session_start();
require_once 'connection/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user data
$user_id = $_SESSION['user_id'];

// Check if the connection is valid
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Prepare and execute query to fetch user data
$query = $conn->prepare("SELECT first_name, last_name, email, contact_no, student_number, username, profile_picture FROM users WHERE id = ?");
if ($query === false) {
    die('MySQL prepare error: ' . $conn->error); // Debugging message for prepare() failure
}
$query->bind_param("i", $user_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();
$query->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact = $_POST['contact_no'];
    $student_number = $_POST['student_number']; // Get student_number from POST
    $new_email = $_POST['email'];
    $username = $_POST['username']; // Get username from POST
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Password verification
    if (!empty($new_password) || !empty($confirm_password)) {
        // Verify old password
        $passwordQuery = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        if ($passwordQuery === false) {
            die('MySQL prepare error: ' . $conn->error);
        }
        $passwordQuery->bind_param("i", $user_id);
        $passwordQuery->execute();
        $passwordResult = $passwordQuery->get_result()->fetch_assoc();
        
        if (password_verify($old_password, $passwordResult['password_hash'])) {
            // Check if new passwords match
            if ($new_password === $confirm_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updatePasswordQuery = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                if ($updatePasswordQuery === false) {
                    die('MySQL prepare error: ' . $conn->error);
                }
                $updatePasswordQuery->bind_param("si", $hashed_new_password, $user_id);
                $updatePasswordQuery->execute();
                $updatePasswordQuery->close();
            } else {
                $error_message = "Passwords do not match.";
            }
        } else {
            $error_message = "Old password is incorrect.";
        }
    }

    // Update other user information (including student_number)
    $updateQuery = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, contact_no = ?, student_number = ?, username = ? WHERE id = ?");
    if ($updateQuery === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $updateQuery->bind_param("ssssssi", $first_name, $last_name, $new_email, $contact, $student_number, $username, $user_id); // Added student_number and username
    $updateQuery->execute();
    $updateQuery->close();

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["profile_picture"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Check file type
        $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                // Update profile picture in the database
                $updatePictureQuery = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                if ($updatePictureQuery === false) {
                    die('MySQL prepare error: ' . $conn->error);
                }
                $updatePictureQuery->bind_param("si", $fileName, $user_id);
                $updatePictureQuery->execute();
                $updatePictureQuery->close();
            }
        }
    }

    header("Location: account.php"); // Redirect to the same page to see changes
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Presto Grub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="uploads/chef-hat.png" type="image/svg+xml">

</head>

<body>
<!-- Include your navbar here -->
<?php include 'sidebar.php'; ?>

<div class="container">
    <h2>Account Information</h2>
    <br>
    <!-- New container for the form -->
    <div class="form-wrapper">
        <div class="form-container">
            <form action="account.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="column">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        
                        <label for="contact_no">Contact:</label>
                        <input type="text" id="contact_no" name="contact_no" 
                               value="<?php echo htmlspecialchars($user['contact_no']); ?>">
                    </div>
                    
                    <div class="column">
                        <label for="student_number">Student Number:</label>
                        <input type="text" id="student_number" name="student_number" 
                               value="<?php echo htmlspecialchars($user['student_number']); ?>" readonly required>
                
                        <label for="old_password">Old Password:</label>
                        <input type="password" id="old_password" name="old_password">
                
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password">
            
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
            
                        <label for="profile_picture" class="file-label">Profile Picture:</label>
                        <input type="file" name="profile_picture" id="profile_picture" class="file-input">
                    </div>
                </div>
                <br>
                <div style="text-align: center;"> <!-- Center-align the container -->
    <button class="save-btn btn-style" type="submit">Save Changes</button><br><br>
    <a href="index.php" class="back-btn btn-style">Back to Home</a>
</div>
            </form>

            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>


<script>

document.addEventListener('DOMContentLoaded', function () {
  const toggleBtn = document.getElementById('toggle-btn');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.querySelector('.main-content');

  toggleBtn.addEventListener('click', function () {
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('shifted');
  });
});

</script>

</body>
</html>