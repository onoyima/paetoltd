<?php
include 'php/config.php';

$token = trim($_GET['token'] ?? '');
$message = '';
$messageType = '';
$validToken = false;
$userEmail = '';

if (empty($token)) {
    $message = 'Invalid or missing reset link.';
    $messageType = 'error';
} else {
    // Verify token
    $stmt = $conn->prepare("SELECT email, expires FROM password_resets WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message = 'Invalid or expired reset link. Please request a new one.';
        $messageType = 'error';
    } else {
        $row = $result->fetch_assoc();
        if (strtotime($row['expires']) < time()) {
            $message = 'This reset link has expired. Please request a new one.';
            $messageType = 'error';
        } else {
            $validToken = true;
            $userEmail = $row['email'];
        }
    }
    $stmt->close();
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPassword = trim($_POST['newPassword'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    if (empty($newPassword) || empty($confirmPassword)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $messageType = 'error';
    } else {
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE userregistration SET password = ? WHERE email = ?");
        $updateStmt->bind_param('ss', $hashedPassword, $userEmail);

        if ($updateStmt->execute()) {
            // Delete used token
            $delStmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $delStmt->bind_param('s', $token);
            $delStmt->execute();
            $delStmt->close();

            $message = 'Password reset successfully! You can now login with your new password.';
            $messageType = 'success';
            $validToken = false; // Hide form after success
        } else {
            $message = 'Error updating password. Please try again.';
            $messageType = 'error';
        }
        $updateStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Paetos Portal</title>
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link class="main-css" href="css/style.css" rel="stylesheet">
    <link href="css/auth.css" rel="stylesheet">
    <style>
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-container { max-width: 500px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .btn-primary { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn-primary:hover { background-color: #0056b3; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        .expired-icon { text-align: center; font-size: 48px; color: #dc3545; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="text-center mb-3">
            <img class="logo-auth" src="images/paetoa.png" alt="Logo">
        </div>
        <h2 style="text-align: center; margin-bottom: 30px;">Reset Password</h2>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($validToken): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="newPassword">New Password:</label>
                <input type="password" id="newPassword" name="newPassword" class="form-control"
                       minlength="6" required placeholder="Enter new password">
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm New Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control"
                       minlength="6" required placeholder="Confirm new password">
            </div>

            <button type="submit" class="btn btn-danger">Set New Password</button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.html">&larr; Back to Login</a>
        </div>
    </div>

    <script>
        document.getElementById('confirmPassword')?.addEventListener('input', function() {
            const password = document.getElementById('newPassword').value;
            if (password !== this.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        <?php if ($messageType === 'success'): ?>
        setTimeout(function() { window.location.href = 'index.html'; }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
