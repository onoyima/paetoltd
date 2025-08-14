<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Array to hold response data
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM userregistration WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Generate a unique reset token
        $token = bin2hex(random_bytes(50));

        // Store the token in the database with an expiration date
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $email, $token, $expires);

        if ($stmt->execute()) {
            // Send the reset link to the user's email
            $resetLink = "http://yourwebsite.com/reset-password.php?token=$token";
            $subject = "Password Reset Request";
            $message = "Click the following link to reset your password: $resetLink";
            $headers = "From: no-reply@yourwebsite.com";

            if (mail($email, $subject, $message, $headers)) {
                $response['success'] = "A password reset link has been sent to your email.";
            } else {
                $response['error'] = "Error: Could not send the email.";
            }
        } else {
            $response['error'] = "Error: Could not store the reset token.";
        }
    } else {
        $response['error'] = "Error: Email address not found.";
    }

    $stmt->close();
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
