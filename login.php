<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Prevent caching (important for security reasons)
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Logged-in users should be redirected to a dashboard or home page
    // This is just an example, replace 'dashboard.php' with your own page
    header("Location: index.php"); 
    exit(); // Make sure no further code is executed after the redirect
}

// Handle error messages from login attempts
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']); // Clear the error message after using it
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <div class="container">
        <!-- Corrected action path -->
        <form id="loginForm" action="function/login_process.php" method="POST">
            <h1>Login</h1>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" class="form-control" required name="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" class="form-control" required name="password">
            </div>
            <div class="register">
                <a href="register.php">Register</a>
                <a href="account/forgot_password.php">Forgot password</a>
            </div>
            <button type="submit" class="btn">Login</button>
            <!-- Go to home button -->
        <button type="button" onclick="window.location.href='index.php'" class="btn">Go to Home</button>
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
<script>
    // Prevent navigation through the back button
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
</script>
<style>
    body {
    font-family: Arial, sans-serif;
    background: linear-gradient(45deg, #2e8b57, #042d86);
    background-size: cover; /* Ensures the image covers the whole background */
    height: 100vh; /* Full height */
    margin: 0;
    }

    .popup-message {
        position: fixed;
        top: 20%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        border: 1px solid #ccc;
        padding: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .popup-message button {
        margin-top: 10px;
    }
    /* Button Styling */
 button.btn {
    display: block; /* Ensures the button behaves as a block-level element */
    width: 40%;
    padding: 10px;
    background-color: green;
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

</style>
