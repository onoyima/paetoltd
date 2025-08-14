<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Array to hold response data
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form inputs
    $email = $_POST['username']; // Assuming username field is used for email
    $password = $_POST['password'];

    // Retrieve user details from database
    $stmt = $conn->prepare("SELECT * FROM userregistration WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password correct, prepare success response
            $response['redirect'] = "dashboard.php"; // Redirect to dashboard

            // Start session and store user data
            session_start();
            $_SESSION['user_id'] = $user['id']; // Store user ID or any other relevant data
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['firstName'] . ' ' . $user['lastName'];

            // Set session timeout to 30 minutes (1800 seconds)
            $_SESSION['timeout'] = time() + 1800; // 30 minutes

            // Remember Me functionality
            if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
                // Set cookie to remember the user (adjust expiry time as needed)
                setcookie('remember_user', $email, time() + (86400 * 30), "/"); // 30 days expiration
            }
        } else {
            // Password incorrect, prepare error response
            $response['error'] = "Error: Incorrect password";
        }
    } else {
        // No user found with the given email, prepare error response
        $response['error'] = "Error: User not found";
    }

    $stmt->close();
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
