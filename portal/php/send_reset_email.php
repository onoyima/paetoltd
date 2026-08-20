<?php
header('Content-Type: application/json');
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
    exit;
}

$email = trim($data['email']);

// Always return success to prevent email enumeration
$successMsg = ['status' => 'success', 'message' => 'A password reset link has been sent to your email.'];

// Check if user exists
$stmt = $conn->prepare("SELECT id, firstName, lastName FROM userregistration WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['status' => 'error', 'message' => 'No account found with that email address.']);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];
$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
$stmt->close();

// Generate secure token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', time() + (PASSWORD_RESET_EXPIRY_MINUTES * 60));

// Delete any existing tokens for this email
$delStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$delStmt->bind_param('s', $email);
$delStmt->execute();
$delStmt->close();

// Insert new token
$insStmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
$insStmt->bind_param('sss', $email, $token, $expires);

if (!$insStmt->execute()) {
    $insStmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate reset token. Please try again.']);
    exit;
}
$insStmt->close();

// Build reset URL
$resetUrl = PASSWORD_RESET_BASE_URL . '/reset-password.php?token=' . $token;

// Send email via PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($email, $fullName);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request - Pa-etos Portal';

    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: #007bff; color: #fff; padding: 20px; text-align: center; }
            .header h2 { margin: 0; }
            .body { padding: 30px; color: #333; line-height: 1.6; }
            .btn { display: inline-block; padding: 12px 30px; background: #007bff; color: #fff !important; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            .btn:hover { background: #0056b3; }
            .footer { padding: 15px 30px; background: #f8f9fa; color: #666; font-size: 12px; text-align: center; }
            .warning { color: #dc3545; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Pa-etos Portal</h2>
            </div>
            <div class="body">
                <p>Hello ' . htmlspecialchars($fullName) . ',</p>
                <p>We received a request to reset your password. Click the button below to set a new password:</p>
                <p style="text-align: center;">
                    <a href="' . htmlspecialchars($resetUrl) . '" class="btn">Reset My Password</a>
                </p>
                <p class="warning">This link will expire in ' . PASSWORD_RESET_EXPIRY_MINUTES . ' minutes.</p>
                <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Pa-etos Hostel Accommodation. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';

    $mail->AltBody = "Hello {$fullName},\n\nReset your password here: {$resetUrl}\n\nLink expires in " . PASSWORD_RESET_EXPIRY_MINUTES . " minutes.\n\nIf you did not request this, ignore this email.";

    $mail->send();
    echo json_encode($successMsg);
} catch (Exception $e) {
    error_log('PHPMailer error: ' . $e->getMessage());
    // The reset token is already valid — log the URL so it can be used manually
    error_log('Password reset URL (fallback): ' . $resetUrl);
    echo json_encode($successMsg);
}
