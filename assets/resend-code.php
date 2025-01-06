<?php
session_start();
require '../connection/connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (!isset($_SESSION['user_email'])) {
    echo "Session expired or invalid user.";
    exit();
}

$email = $_SESSION['user_email'];
$verification_code = random_int(100000, 999999);
$_SESSION['verification_code'] = $verification_code;

// Get the user_id from the email
$query = "SELECT id FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $user_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$user_id) {
    echo "User not found.";
    exit();
}

// Update the verification code in 'email_verifications' table
$expire_at = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Code expires after 15 minutes
$query_verification = "UPDATE email_verifications SET verification_code = ?, expire_at = ?, is_verified = 0, created_at = NOW() 
                       WHERE user_id = ?";
$stmt_verification = mysqli_prepare($conn, $query_verification);
mysqli_stmt_bind_param($stmt_verification, "ssi", $verification_code, $expire_at, $user_id);
mysqli_stmt_execute($stmt_verification);

// Send the verification code to the user's email
$mail = new PHPMailer(true);
try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'raphiel322@gmail.com'; // Replace with your email
    $mail->Password = 'uqrx gbyb wkca aulr';  // Replace with your email password or app-specific password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email content
    $mail->setFrom('raphiel322@gmail.com', 'Your App');
    $mail->addAddress($email);  // Add user's email as recipient

    $mail->isHTML(true);
    $mail->Subject = 'Your Verification Code';
    $mail->Body = "Your verification code is <b>$verification_code</b>. It will expire in 15 minutes.";

    // Send the email
    if ($mail->send()) {
        echo 'Verification code sent to your email.';
    } else {
        echo 'Failed to send verification code.';
    }
} catch (Exception $e) {
    // Log error details if sending fails
    error_log("Error sending verification code: " . $mail->ErrorInfo);
    echo 'Error sending verification code: ' . $mail->ErrorInfo;
}
?>
