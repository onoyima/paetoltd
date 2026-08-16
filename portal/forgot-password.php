<?php

include 'php/config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $secretQuestion = trim($_POST['secretQuestion'] ?? '');
    $secretAnswer = trim($_POST['secretAnswer'] ?? '');
    $newPassword = trim($_POST['newPassword'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    
    // Validation
    if (empty($email) || empty($secretQuestion) || empty($secretAnswer) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $messageType = 'error';
    } else {
        try {
            // Check if user exists with provided email and secret answer
            $stmt = $conn->prepare("SELECT id FROM userregistration WHERE email = ? AND secret_question = ? AND secret_answer = ?");
            $stmt->bind_param("sss", $email, $secretQuestion, $secretAnswer);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $userId = $user['id'];
                
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE userregistration SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $userId);
                
                if ($updateStmt->execute()) {
                    $message = 'Password reset successfully! You can now login with your new password.';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating password. Please try again.';
                    $messageType = 'error';
                }
                $updateStmt->close();
            } else {
                $message = 'Invalid email or secret answer.';
                $messageType = 'error';
            }
            $stmt->close();
        } catch (Exception $e) {
            // Log the error for debugging
            error_log('Password reset error: ' . $e->getMessage());
            
            // Show user-friendly message
            $message = 'Database error occurred. Please try again later.' . $e->getMessage();
            $messageType = 'error';
        }
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
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
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
        
        <?php if ($messageType !== 'success'): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="secretQuestion">Secret Question:</label>
                <select id="secretQuestion" name="secretQuestion" class="form-control p-1" required>
                    <option value="">Select a secret question</option>
                    <option value="What is your mother's maiden name?" 
                            <?php echo (($_POST['secretQuestion'] ?? '') === 'What is your mother\'s maiden name?') ? 'selected' : ''; ?>>
                        What is your mother's maiden name?
                    </option>
                    <option value="What was the name of your first pet?" 
                            <?php echo (($_POST['secretQuestion'] ?? '') === 'What was the name of your first pet?') ? 'selected' : ''; ?>>
                        What was the name of your first pet?
                    </option>
                    <option value="What is your favorite book?" 
                            <?php echo (($_POST['secretQuestion'] ?? '') === 'What is your favorite book?') ? 'selected' : ''; ?>>
                        What is your favorite book?
                    </option>
                    <option value="What is your favorite pet?" 
                            <?php echo (($_POST['secretQuestion'] ?? '') === 'What is your favorite pet?') ? 'selected' : ''; ?>>
                        What is your favorite pet?
                    </option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="secretAnswer">Secret Answer:</label>
                <input type="text" id="secretAnswer" name="secretAnswer" class="form-control" 
                       value="<?php echo htmlspecialchars($_POST['secretAnswer'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="newPassword">New Password:</label>
                <input type="password" id="newPassword" name="newPassword" class="form-control" 
                       minlength="6" required>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" 
                       minlength="6" required>
            </div>
            
            <button type="submit" class="btn btn-danger">Reset Password</button>
        </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.html">← Back to Login</a>
        </div>
    </div>
    
    <script>
        // Client-side password confirmation validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Auto-redirect on success
        <?php if ($messageType === 'success'): ?>
        setTimeout(function() {
            window.location.href = 'index.html';
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>