<?php
session_start();
require_once '../connection/connection.php'; // Database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // PHPMailer's autoload file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists in the users table
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
        header('Location: forgot_password.php');
        exit();
    }
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate a unique reset token
        $token = bin2hex(random_bytes(50)); // Secure random token
        $expires = time() + 1800; // Token expires in 30 minutes

        // Store the token in the database
        $insertTokenQuery = "INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertTokenQuery);
        if (!$insertStmt) {
            $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
            header('Location: forgot_password.php');
            exit();
        }
        mysqli_stmt_bind_param($insertStmt, "ssi", $email, $token, $expires);
        mysqli_stmt_execute($insertStmt);

        // Construct the reset link
// Construct the reset link for the hosted environment
$resetLink = "https://prestogrub.42web.io/account/reset_password.php?token=" . urlencode($token);


        // Setup PHPMailer
        $mail = new PHPMailer(true); // Create a PHPMailer instance
        try {
            // Server settings
            $mail->isSMTP();                                      // Use SMTP
            $mail->Host = 'smtp.gmail.com';                       // SMTP server
            $mail->SMTPAuth = true;                               // Enable authentication
            $mail->Username = 'raphiel322@gmail.com';             // SMTP username (replace with actual email)
            $mail->Password = 'hpzu mfan kzsk hdyj';                // SMTP password (replace with actual password or app-specific password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Use TLS
            $mail->Port = 587;                                    // SMTP port
            
            // Recipients
            $mail->setFrom('raphiel322@gmail.com', 'PrestoGrub');  // Sender's email
            $mail->addAddress($email);                            // Recipient's email
            
            // Email content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'PrestoGrub Password Reset Request';
            $mail->Body    = "To reset your password, please click the link below:<br><br><a href='$resetLink'>$resetLink</a>";
            
            // Send email
            $mail->send();
            $_SESSION['success_message'] = "A password reset link has been sent to your email.";
            header('Location: ../login.php'); // Redirect to login page
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to send email. Error: " . $mail->ErrorInfo;
            header('Location: forgot_password.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = "No account found with that email address.";
        header('Location: forgot_password.php');
        exit();
    }
}
?>