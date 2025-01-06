<?php
session_start();
require_once '../connection/connection.php';

// Check if the user_id is passed via GET or already stored in the session
if (isset($_GET['user_id'])) {
    $_SESSION['user_id'] = $_GET['user_id'];
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Missing user ID for verification.";
    header("Location: ../register.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST request for verifying the code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = trim($_POST['verification_code']);

    if (empty($verification_code)) {
        $_SESSION['error'] = "Please enter the verification code.";
        header("Location: email_verification.php");
        exit();
    }

    // Check if the verification code matches
    $query = "SELECT * FROM email_verifications 
              WHERE user_id = ? AND verification_code = ? AND is_verified = 0 
              AND expire_at > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $verification_code);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header("Location: email_verification.php");
        exit();
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Mark email as verified
        $updateVerificationQuery = "UPDATE email_verifications SET is_verified = 1 WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateVerificationQuery);
        $updateStmt->bind_param("i", $user_id);
        $updateStmt->execute();

        // Update the user's verification and activation status
        $updateUserQuery = "UPDATE users SET verified = 1, is_active = 1 WHERE id = ?";
        $updateUserStmt = $conn->prepare($updateUserQuery);
        $updateUserStmt->bind_param("i", $user_id);
        $updateUserStmt->execute();

        $_SESSION['success'] = "Your email has been verified successfully!";
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid or expired verification code.";
        header("Location: email_verification.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        h1 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
        .success-message {
            color: green;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:disabled {
            background-color: #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Email Verification</h1>
    <?php
    if (isset($_SESSION['error'])) {
        echo "<p class='error-message'>{$_SESSION['error']}</p>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<p class='success-message'>{$_SESSION['success']}</p>";
        unset($_SESSION['success']);
    }
    ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="verification_code">Verification Code</label>
            <input type="text" id="verification_code" name="verification_code" required>
        </div>
        <button type="submit">Verify</button>
    </form>
    <form method="POST" action="resend_verification.php" style="margin-top: 15px;">
        <button type="submit">Resend Code</button>
    </form>
</div>
</body>
</html>
