<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
    exit;
}

include 'config.php';

$userId = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

$currentPassword = $input['currentPassword'] ?? '';
$newPassword = $input['newPassword'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters.']);
    exit;
}

if ($currentPassword === $newPassword) {
    echo json_encode(['status' => 'error', 'message' => 'New password must be different from current password.']);
    exit;
}

// Verify current password
$stmt = $conn->prepare("SELECT password FROM userregistration WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();

if (!password_verify($currentPassword, $row['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
    exit;
}

// Update password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $conn->prepare("UPDATE userregistration SET password = ? WHERE id = ?");
$updateStmt->bind_param('si', $hashedPassword, $userId);

if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password changed successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
}
$updateStmt->close();
$conn->close();
