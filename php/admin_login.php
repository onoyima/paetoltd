<?php
include 'config.php';
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form inputs
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // Prepare and execute the query to retrieve user details from the database
    $stmt = $conn->prepare("SELECT id, email, username, password, role FROM admin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user was found with the given email
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password correct, start session and store user data
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Set session timeout to 30 minutes (1800 seconds)
            $_SESSION['timeout'] = time() + 1800;

            // Remember Me functionality
            if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
                setcookie('remember_user', $email, time() + (86400 * 30), "/"); // 30 days expiration
            }

            // Redirect to dashboard
            header('Location: ../admin-dashboard.php');
            exit();
        } else {
            // Password incorrect, prepare error response
            $error = "Error: Incorrect password";
        }
    } else {
        // No user found with the given email, prepare error response
        $error = "Error: User not found";
    }

    $stmt->close();
}

$conn->close();

if (isset($error)) {
    echo $error;
}
?>