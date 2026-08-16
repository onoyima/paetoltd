<?php
require_once __DIR__ . '/rbac.php';

// Only a Super Admin may create new administrator accounts.
pt_require('create_admin');

include 'config.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : 'admin';
    $regDate = date('Y-m-d H:i:s');

    // Validate required fields
    if ($username === '' || $email === '' || $password === '') {
        $response['error'] = 'Error: All fields are required.';
    } elseif (!pt_valid_admin_role($role)) {
        // Only allow known roles to be assigned
        $response['error'] = 'Error: Invalid role selected.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = 'Error: Invalid email address.';
    } elseif (strlen($password) < 8) {
        $response['error'] = 'Error: Password must be at least 8 characters long.';
    } elseif ($role === 'superadmin' && $_SESSION['role'] !== 'superadmin') {
        // Non-super admins must never be able to create another super admin
        $response['error'] = 'Error: You do not have permission to create a Super Admin.';
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $response['error'] = "Error: Email already exists";
        } else {
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO admin (username, email, password, reg_date, updation_date, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $username, $email, $hashed, $regDate, $regDate, $role);

            if ($stmt->execute()) {
                $response['success'] = "Admin account created successfully with role '" . pt_role_label($role) . "'.";
            } else {
                $response['error'] = "Error: " . $stmt->error;
            }
        }

        $stmt->close();
    }
} else {
    $response['error'] = 'Error: Invalid request method.';
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
