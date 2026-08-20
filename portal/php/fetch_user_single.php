<?php
header('Content-Type: application/json');
include 'auth_admin.php';
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user id']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, regNo, firstName, middleName, lastName, gender, department, level, contactNo, parentPhone, email, userImage FROM userregistration WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Student not found']);
        exit;
    }
    if (!empty($row['userImage'])) {
        $row['userImage'] = base64_encode($row['userImage']);
    }
    echo json_encode(['status' => 'success', 'user' => $row]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
