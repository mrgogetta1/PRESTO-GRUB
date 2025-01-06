<?php
session_start();
require_once '../connection/connection.php'; // Your database connection

// Validate token
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Check if token exists and is valid
    $query = "SELECT * FROM password_resets WHERE token = ? AND expires > ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $token, time()); // Use current time for expiry comparison
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Token is valid, allow password reset
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = mysqli_real_escape_string($conn, $_POST['password']);
            $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm_password']);
            
            // Check if the passwords match
            if ($password !== $confirmPassword) {
                $_SESSION['error_message'] = "Passwords do not match. Please try again.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Get the email from the token
                $row = $result->fetch_assoc();
                $email = $row['email'];
                
                // Update the user's password in the database
                $updateQuery = "UPDATE users SET password_hash = ? WHERE email = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param('ss', $passwordHash, $email);
                
                if ($updateStmt->execute()) {
                    // Delete the token from the password_resets table
                    $deleteQuery = "DELETE FROM password_resets WHERE token = ?";
                    $deleteStmt = $conn->prepare($deleteQuery);
                    $deleteStmt->bind_param('s', $token);
                    $deleteStmt->execute();
                    
                    $_SESSION['success_message'] = "Your password has been successfully reset.";
                    header('Location: ../login.php'); // Redirect to login page
                    exit();
                } else {
                    $_SESSION['error_message'] = "Failed to reset password. Please try again.";
                }
            }
        }
    } else {
        $_SESSION['error_message'] = "Invalid or expired token.";
        header('Location: forgot_password.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = "No reset token found.";
    header('Location: forgot_password.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: green;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: darkgreen;
        }

        .popup-message {
            background-color: #ffcccc;
            color: #ff0000;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }

        .popup-message.success {
            background-color: #ccffcc;
            color: #008000;
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="reset_password.php?token=<?php echo $_GET['token']; ?>" method="POST">
            <h1>Reset Your Password</h1>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="popup-message"><?php echo $_SESSION['error_message']; ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="popup-message success"><?php echo $_SESSION['success_message']; ?></div>
        <?php endif; ?>

        <?php
        // Unset the session messages after they are displayed
        unset($_SESSION['error_message']);
        unset($_SESSION['success_message']);
        ?>
    </div>
</body>
</html>
