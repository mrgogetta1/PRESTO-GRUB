<?php
session_start();
require_once '../connection/connection.php';  // Ensure this path is correct
require_once '../vendor/autoload.php';  // Autoload PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the user is logged in and the user ID is set
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to request a verification email.";
    header("Location: register.php");  // Redirect to the registration page
    exit();
}

$user_id = $_SESSION['user_id'];  // Retrieve the user ID from the session

// Check if user is registered but not verified
$query = "SELECT * FROM users WHERE id = ? AND verified = 0";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user is not found or already verified, show an error
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "User not found or already verified.";
        header("Location: email_verification.php?user_id=$user_id");  // Redirect back to email verification page
        exit();
    }

    // Generate a new verification code
    $verificationCode = bin2hex(random_bytes(3));

    // Update the verification code in the database
    $updateQuery = "UPDATE email_verifications SET verification_code = ?, created_at = NOW(), is_verified = 0 WHERE user_id = ?";
    if ($updateStmt = $conn->prepare($updateQuery)) {
        $updateStmt->bind_param("si", $verificationCode, $user_id);
        $updateStmt->execute();
    }

    // Fetch user details (to send the email)
    $userQuery = "SELECT first_name, email FROM users WHERE id = ?";
    if ($userStmt = $conn->prepare($userQuery)) {
        $userStmt->bind_param("i", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();

        $firstName = $user['first_name'];
        $email = $user['email'];

        // Send the verification email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'raphiel322@gmail.com'; // Your email address
            $mail->Password = 'hpzu mfan kzsk hdyj'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('raphiel@gmail.com', 'Your Application'); // Sender's email
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Resend Email Verification';
            $mail->Body = "Hello $firstName,<br><br>
                           We noticed you didn't verify your email yet. Please verify your email by entering the following code:<br><br>
                           <b>$verificationCode</b><br><br>
                           Thank you!";

            $mail->send();
            $_SESSION['success'] = "Verification email has been resent.";
            header("Location: email_verification.php?user_id=$user_id"); // Redirect back to email verification page with user ID
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            header("Location: email_verification.php?user_id=$user_id"); // Redirect to email_verification.php
            exit();
        }
    }
} else {
    $_SESSION['error'] = "Database error. Please try again later.";
    header("Location: email_verification.php?user_id=$user_id");  // Redirect back to email_verification.php
    exit();
}
?>