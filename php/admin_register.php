<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Array to hold response data
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form inputs
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt the password
    $regDate = date('Y-m-d H:i:s');
    $role = 'admin'; // Set the role for new admins

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM admin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email already exists, prepare error response
        $response['error'] = "Error: Email already exists";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO admin (username, email, password, reg_date, updation_date, role) VALUES (?, ?, ?, ?, ?, ?)");
        // Assuming `updation_date` should be the same as `reg_date` upon registration
        $stmt->bind_param('ssssss', $username, $email, $password, $regDate, $regDate, $role);

        if ($stmt->execute()) {
            // Success response
            $response['success'] = "Signup successful! You can now <a href='../admin_login.html'>login</a>.";
        } else {
            // Error response
            $response['error'] = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
