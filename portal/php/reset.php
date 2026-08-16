<?php
require_once __DIR__ . '/rbac.php';

session_start();

// Only admins with the reset_password permission may reset account passwords
pt_require('reset_password');

include 'config.php';

// Array to hold response data
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form inputs
    $email = $_POST['email'];
    $type = $_POST['type']; // Determine if the reset is for 'admin' or 'user'
    $defaultPassword = 'welcome'; // Default password
    $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT); // Encrypt the default password
    $updationDate = date('Y-m-d H:i:s');

    if ($type === 'admin') {
        // Handle admin password reset
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email exists, update the password
            $stmt->bind_result($userId);
            $stmt->fetch();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE admin SET password = ?, updation_date = ? WHERE id = ?");
            $stmt->bind_param('ssi', $hashedPassword, $updationDate, $userId);

            if ($stmt->execute()) {
                // Success response
                $response['success'] = "Admin password reset successfully! The new password is 'welcome'.";
            } else {
                // Error response
                $response['error'] = "Error: " . $stmt->error;
            }
        } else {
            // Email does not exist
            $response['error'] = "Error: No admin found with this email.";
        }
    } elseif ($type === 'user') {
        // Handle user password reset
        $stmt = $conn->prepare("SELECT id FROM userregistration WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email exists, update the password
            $stmt->bind_result($userId);
            $stmt->fetch();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE userregistration SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashedPassword, $userId);

            if ($stmt->execute()) {
                // Success response
                $response['success'] = "User password reset successfully! The new password is 'welcome'.";
            } else {
                // Error response
                $response['error'] = "Error: " . $stmt->error;
            }
        } else {
            // Email does not exist
            $response['error'] = "Error: No user found with this email.";
        }
    } else {
        $response['error'] = "Error: Invalid reset type specified.";
    }

    if (isset($stmt)) {
        $stmt->close();
    }
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
