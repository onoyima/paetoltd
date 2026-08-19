<?php
require_once __DIR__ . '/rbac.php';
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$isAdmin = pt_is_admin();
$userId = $isAdmin ? (int)($_POST['userId'] ?? 0) : (int)$_SESSION['user_id'];

if ($userId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user id']);
    exit;
}

$regNo = trim($_POST['regNo'] ?? '');
$firstName = trim($_POST['firstName'] ?? '');
$middleName = trim($_POST['middleName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$department = trim($_POST['department'] ?? '');
$level = trim($_POST['level'] ?? '');
$contactNo = trim($_POST['contactNo'] ?? '');
$parentPhone = trim($_POST['parentPhone'] ?? '');
$email = trim($_POST['email'] ?? '');

$required = ['regNo' => $regNo, 'firstName' => $firstName, 'lastName' => $lastName, 'gender' => $gender, 'contactNo' => $contactNo, 'email' => $email];
foreach ($required as $field => $value) {
    if ($value === '') {
        echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
        exit;
    }
}

// Handle image upload
$imageData = null;
if (isset($_FILES['userImage']) && $_FILES['userImage']['error'] === UPLOAD_ERR_OK) {
    $imageData = file_get_contents($_FILES['userImage']['tmp_name']);
}

if ($imageData !== null) {
    $sql = "UPDATE userregistration SET
            regNo=?, firstName=?, middleName=?, lastName=?,
            gender=?, department=?, level=?,
            contactNo=?, parentPhone=?, email=?,
            userImage=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $null = null;
        $stmt->bind_param("ssssssssssbi",
            $regNo, $firstName, $middleName, $lastName,
            $gender, $department, $level,
            $contactNo, $parentPhone, $email,
            $null, $userId
        );
        $stmt->send_long_data(10, $imageData);
    }
} else {
    $sql = "UPDATE userregistration SET
            regNo=?, firstName=?, middleName=?, lastName=?,
            gender=?, department=?, level=?,
            contactNo=?, parentPhone=?, email=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssssssssi",
            $regNo, $firstName, $middleName, $lastName,
            $gender, $department, $level,
            $contactNo, $parentPhone, $email,
            $userId
        );
    }
}

if ($stmt && $stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update record']);
}

if ($stmt) {
    $stmt->close();
}
$conn->close();
?>