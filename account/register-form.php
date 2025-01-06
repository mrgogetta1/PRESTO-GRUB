<?php
session_start();
require_once '../connection/connection.php';
require_once '../vendor/autoload.php'; // Ensure the correct path to autoload
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $username = trim($_POST['username']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $studentNumber = trim($_POST['student_number']); // Updated to student_number
    $contactNo = trim($_POST['contact_no']);
    $course = trim($_POST['course']);
    $section = trim($_POST['section']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert user details into 'users' table
        $query = "INSERT INTO users (username, first_name, last_name, email, password_hash, student_number, contact_no, course, section, registered_at, verified) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssss", $username, $firstName, $lastName, $email, $password, $studentNumber, $contactNo, $course, $section);

        if (!$stmt->execute()) {
            throw new Exception("Error inserting user: " . $stmt->error);
        }

        $userId = $stmt->insert_id; // Get the ID of the newly inserted user

        // Generate a 6-character random verification code
        $verificationCode = bin2hex(random_bytes(3)); // Secure random 6-character code (3 bytes)

        // Insert the verification code into 'email_verifications' table
        $verificationQuery = "INSERT INTO email_verifications (user_id, verification_code, created_at, expire_at, is_verified) 
                              VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR), 0)";
        $verificationStmt = $conn->prepare($verificationQuery);
        $verificationStmt->bind_param("is", $userId, $verificationCode);

        if (!$verificationStmt->execute()) {
            throw new Exception("Error inserting verification code: " . $verificationStmt->error);
        }

        // Send verification email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'raphiel322@gmail.com'; // Replace with your email
            $mail->Password = 'hpzu mfan kzsk hdyj'; // Use environment variables for security
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipient settings
            $mail->setFrom('raphiel322@gmail.com', 'PrestoGrub'); // Use a valid email
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body = "Hello $firstName,<br><br>
                           Please verify your email by entering the following code:<br><br>
                           <b>$verificationCode</b><br><br>
                           Thank you!";

            $mail->send();
        } catch (Exception $e) {
            throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        }

        // Commit transaction
        $conn->commit();

        // Redirect to email_verification.php with the user ID and success message
       // Redirect to email_verification.php with the user ID and success message
        $_SESSION['success'] = "Registration successful! Check your email for the verification code.";
        header("Location: ../account/email_verification.php?user_id=$userId");
        exit;


    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        // Log error
        error_log($e->getMessage());

        // Store error in session and redirect
        $_SESSION['error'] = $e->getMessage();
        header("Location: register1");
        exit;
    }
}
?>