<?php
require_once __DIR__ . '/rbac.php';

session_start();

include 'config.php'; // Include your database connection configuration

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Check if POST data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Admin may update any student; students may only update themselves (prevents IDOR)
    $isAdmin = pt_is_admin();
    $userId = $isAdmin ? (int)($_POST['userId'] ?? 0) : (int)$_SESSION['user_id'];

    if ($userId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user id']);
        exit;
    }

    $userData = isset($_POST['userData']) && is_array($_POST['userData']) ? $_POST['userData'] : array();

    $required = array('regNo', 'firstName', 'middleName', 'lastName', 'gender', 'contactNo', 'email');
    foreach ($required as $field) {
        if (!isset($userData[$field]) || trim((string)$userData[$field]) === '') {
            echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
            exit;
        }
    }

    // Prepare SQL statement to update user data
    $sql = "UPDATE userregistration SET
            regNo = ?,
            firstName = ?,
            middleName = ?,
            lastName = ?,
            gender = ?,
            contactNo = ?,
            email = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (
        $stmt &&
        $stmt->bind_param(
            "sssssssi",
            $userData['regNo'],
            $userData['firstName'],
            $userData['middleName'],
            $userData['lastName'],
            $userData['gender'],
            $userData['contactNo'],
            $userData['email'],
            $userId
        ) &&
        $stmt->execute()
    ) {
        // Successful update
        echo json_encode(['status' => 'success']);
    } else {
        // Error in SQL statement or execution
        echo json_encode(['status' => 'error', 'message' => 'Failed to update the record.']);
    }

    // Close statement and connection
    if ($stmt) {
        $stmt->close();
    }
    $conn->close();
} else {
    // Handle invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
