<?php
require_once __DIR__ . '/rbac.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Admin login required.']);
    exit;
}

// Re-verify the admin's current role from the database so stale or tampered
// sessions can never escalate privileges.
include __DIR__ . '/config.php';

$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Admin login required.']);
    exit;
}

$dbRole = $result->fetch_assoc()['role'];
$stmt->close();

if (!pt_valid_admin_role($dbRole)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Admin login required.']);
    exit;
}

// Keep the session role in sync with the database.
$_SESSION['role'] = $dbRole;

if (isset($_SESSION['timeout']) && $_SESSION['timeout'] < time()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
    exit;
}

$_SESSION['timeout'] = time() + 1800;
?>
