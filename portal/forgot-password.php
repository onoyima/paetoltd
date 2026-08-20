<?php
include 'php/config.php';

$message = '';
$messageType = '';

// Handle secret question reset (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['secretQuestion'])) {
    $email = trim($_POST['email'] ?? '');
    $secretQuestion = trim($_POST['secretQuestion'] ?? '');
    $secretAnswer = trim($_POST['secretAnswer'] ?? '');
    $newPassword = trim($_POST['newPassword'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

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
            $stmt = $conn->prepare("SELECT id FROM userregistration WHERE email = ? AND secret_question = ? AND secret_answer = ?");
            $stmt->bind_param("sss", $email, $secretQuestion, $secretAnswer);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $userId = $user['id'];
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
            error_log('Password reset error: ' . $e->getMessage());
            $message = 'Database error occurred. Please try again later.';
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
        .nav-tabs { border-bottom: 2px solid #dee2e6; margin-bottom: 20px; }
        .nav-tabs .nav-link { border: none; color: #666; padding: 10px 20px; cursor: pointer; font-size: 14px; border-bottom: 2px solid transparent; margin-bottom: -2px; }
        .nav-tabs .nav-link.active { color: #007bff; border-bottom-color: #007bff; font-weight: bold; }
        .nav-tabs .nav-link:hover { color: #0056b3; border-bottom-color: #ccc; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .loading { display: none; }
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
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-tab="secret" onclick="switchTab('secret')">Secret Question</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-tab="email" onclick="switchTab('email')">Email Reset</a>
            </li>
        </ul>

        <!-- Secret Question Tab -->
        <div id="tab-secret" class="tab-pane active">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="sqEmail">Email Address:</label>
                    <input type="email" id="sqEmail" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="secretQuestion">Secret Question:</label>
                    <select id="secretQuestion" name="secretQuestion" class="form-control p-1" required>
                        <option value="">Select a secret question</option>
                        <option value="What is your mother's maiden name?"
                                <?php echo (($_POST['secretQuestion'] ?? '') === "What is your mother's maiden name?") ? 'selected' : ''; ?>>
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
        </div>

        <!-- Email Reset Tab -->
        <div id="tab-email" class="tab-pane">
            <form id="emailResetForm" onsubmit="sendResetEmail(event)">
                <div class="form-group">
                    <label for="resetEmail">Email Address:</label>
                    <input type="email" id="resetEmail" name="email" class="form-control"
                           placeholder="Enter your registered email" required>
                </div>
                <button type="submit" class="btn btn-primary" id="emailSubmitBtn">
                    <span class="btn-text">Send Reset Link</span>
                    <span class="loading" id="emailLoading">Sending...</span>
                </button>
            </form>
        </div>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.html">&larr; Back to Login</a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
            document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');
        }

        function sendResetEmail(e) {
            e.preventDefault();
            var email = document.getElementById('resetEmail').value.trim();
            var btn = document.getElementById('emailSubmitBtn');
            var btnText = btn.querySelector('.btn-text');
            var loading = document.getElementById('emailLoading');

            if (!email) { return; }

            btn.disabled = true;
            btnText.style.display = 'none';
            loading.style.display = 'inline';

            fetch('php/send_reset_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btnText.style.display = 'inline';
                loading.style.display = 'none';

                var msgDiv = document.createElement('div');
                msgDiv.className = 'message ' + (data.status === 'success' ? 'success' : 'error');
                msgDiv.textContent = data.message;

                var existing = document.querySelector('.message');
                if (existing) existing.remove();
                document.getElementById('tab-email').insertBefore(msgDiv, document.getElementById('emailResetForm'));
            })
            .catch(function() {
                btn.disabled = false;
                btnText.style.display = 'inline';
                loading.style.display = 'none';

                var msgDiv = document.createElement('div');
                msgDiv.className = 'message error';
                msgDiv.textContent = 'Network error. Please try again.';

                var existing = document.querySelector('.message');
                if (existing) existing.remove();
                document.getElementById('tab-email').insertBefore(msgDiv, document.getElementById('emailResetForm'));
            });
        }

        document.getElementById('confirmPassword')?.addEventListener('input', function() {
            var password = document.getElementById('newPassword')?.value || '';
            this.setCustomValidity(password !== this.value ? 'Passwords do not match' : '');
        });

        <?php if ($messageType === 'success'): ?>
        setTimeout(function() { window.location.href = 'index.html'; }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
