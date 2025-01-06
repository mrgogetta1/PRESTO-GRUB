<?php
session_start();

// Initialize the error_message variable
$error_message = '';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if there is an error message in the session
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Remove the error message from the session
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/form.css">
    <style>
        /* Body background styling */
body {
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    background: linear-gradient(45deg, #2e8b57, #042d86);
    background-size: cover; /* Ensures the image covers the entire screen */
    background-position: center; /* Centers the image */
    background-repeat: no-repeat; /* Prevents the image from repeating */
}

        /* Container for the form */
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        /* Form Title */
        h1 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 20px;
        }

        /* Label styling */
        label {
            font-size: 14px;
            color: black;
            margin-bottom: 5px;
            display: block;
        }

        /* Input field styling */
        input[type="email"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        /* Button Styling */
button.btn {
    display: block; /* Ensures the button behaves as a block-level element */
    width: 60%;
    padding: 10px;
    background: linear-gradient(45deg, #2e8b57, #042d86);
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
    margin: 10px auto; /* Center the button and add spacing between buttons */
    text-align: center; /* Centers text inside the button */
}

button.btn:hover {
    background-color: darkgreen;
}


        /* Popup Message */
        .popup-message {
            background-color: #ffcccc;
            color: #ff0000;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .popup-message button {
            background: none;
            border: none;
            color: #ff0000;
            font-size: 16px;
            cursor: pointer;
        }

        .popup-message button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="forgotPasswordForm" action="forgot_password_process.php" method="POST">
            <h1>Forgot Password</h1>
            <div class="form-group">
                <label for="email">Enter your email</label>
                <input type="email" id="email" class="form-control" required name="email">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn">Send Reset Link</button>

            <!-- Back Button -->
            <button type="button" class="btn" onclick="window.location.href='../index.php'">Back to Home</button>
        </form>

        <?php if ($error_message): ?>
            <div class="popup-message" id="popupMessage">
                <span><?php echo htmlspecialchars($error_message); ?></span>
                <button onclick="document.getElementById('popupMessage').style.display='none'">Close</button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>