<?php
session_start();
require_once '../connection/connection.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';  // Include the PHPMailer library

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = $_POST['verification_code'];
    $email = $_SESSION['user_email'];

    // Get the user_id from the email
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($user_id) {
        // Check the verification code in the 'email_verifications' table
        $query_verification = "SELECT verification_code, expire_at, is_verified 
                               FROM email_verifications WHERE user_id = ? AND is_verified = 0 ORDER BY created_at DESC LIMIT 1";
        $stmt_verification = mysqli_prepare($conn, $query_verification);
        mysqli_stmt_bind_param($stmt_verification, "i", $user_id);
        mysqli_stmt_execute($stmt_verification);
        mysqli_stmt_bind_result($stmt_verification, $db_code, $expire_at, $is_verified);
        mysqli_stmt_fetch($stmt_verification);
        mysqli_stmt_close($stmt_verification);

        // Debugging: log values for verification
        error_log("Verification code from DB: $db_code");
        error_log("Entered code: $entered_code");
        error_log("Expiry: $expire_at");
        error_log("Current Time: " . date('Y-m-d H:i:s'));

        // Check if the entered code is valid, not expired, and not already verified
        if ($db_code && !$is_verified && $entered_code == $db_code && strtotime($expire_at) > time()) {
            // Code is correct and not expired
            // Update the 'is_verified' field in email_verifications table
            $query_update = "UPDATE email_verifications SET is_verified = 1 WHERE user_id = ?";
            $stmt_update = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt_update, "i", $user_id);
            mysqli_stmt_execute($stmt_update);

            // Update the 'is_active' field in the 'users' table
            $query_user_update = "UPDATE users SET is_active = 1 WHERE id = ?";
            $stmt_user_update = mysqli_prepare($conn, $query_user_update);
            mysqli_stmt_bind_param($stmt_user_update, "i", $user_id);
            mysqli_stmt_execute($stmt_user_update);

            // Start the session and set user_logged_in to true
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user_id;  // Store the user_id in session for later use

            // Redirect to the home page (index.php)
            header('Location: ../index.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid or expired verification code.";
            header('Location: verify.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = "User not found.";
        header('Location: verify.php');
        exit();
    }
} else {
    // If not POST, generate and send the verification code

    $email = $_SESSION['user_email'];
    $verification_code = random_int(100000, 999999);
    $_SESSION['verification_code'] = $verification_code;
    $expire_at = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Set expiration time

    // Get the user_id from the email
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Clean up old unverified codes before inserting a new one
    $query_cleanup = "DELETE FROM email_verifications WHERE user_id = ? AND is_verified = 0";
    $stmt_cleanup = mysqli_prepare($conn, $query_cleanup);
    mysqli_stmt_bind_param($stmt_cleanup, "i", $user_id);
    mysqli_stmt_execute($stmt_cleanup);
    mysqli_stmt_close($stmt_cleanup);

    // Insert the new verification code into the email_verifications table
    $query_verification = "INSERT INTO email_verifications (user_id, verification_code, expire_at, is_verified, created_at)
                           VALUES (?, ?, ?, 0, NOW())";
    $stmt_verification = mysqli_prepare($conn, $query_verification);
    mysqli_stmt_bind_param($stmt_verification, "iss", $user_id, $verification_code, $expire_at);
    mysqli_stmt_execute($stmt_verification);
    mysqli_stmt_close($stmt_verification);

    // Send the verification code via email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'raphiel322@gmail.com';  // Replace with your email
        $mail->Password = 'yump ghtb idgw yhik';   // Replace with your email password or app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('raphiel322@gmail.com', 'PrestoGrub');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is <b>$verification_code</b>. It will expire in 15 minutes.";

        $mail->send();
        echo 'Verification code sent to your email.';
    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
}
?>

<!-- HTML form for entering the verification code -->
<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
    <link rel="stylesheet" href="">
    
</head>
<body>
    <h1>Email Verification</h1>
    <?php if (isset($_SESSION['error_message'])): ?>
        <p style="color: red;"><?= $_SESSION['error_message'] ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="verification_code">Enter Verification Code</label>
        <input type="text" name="verification_code" required>
        <button type="submit">Verify</button>
    </form>
    <button id="resend-btn" onclick="resendCode()" disabled>Resend Code</button>

    <script>
        let resendBtn = document.getElementById('resend-btn');
        let timer = 15;

        function countdown() {
            if (timer > 0) {
                resendBtn.disabled = true;
                resendBtn.innerText = `Resend Code (${timer}s)`;
                timer--;
                setTimeout(countdown, 1000);
            } else {
                resendBtn.disabled = false;
                resendBtn.innerText = 'Resend Code';
            }
        }

        function resendCode() {
            fetch('../assets/resend-code.php')
                .then(() => {
                    alert('New code sent to your email.');
                    timer = 15;
                    countdown();
                });
        }

        countdown();
    </script>
</body>
</html>
