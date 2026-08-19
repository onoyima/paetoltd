<?php
include 'auth_admin.php';
include 'config.php';

try {
    $stmt = $conn->prepare("SELECT id, regNo, firstName, middleName, lastName, gender, department, level, contactNo, parentPhone, email, userImage FROM userregistration");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = array();
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['userImage'])) {
            $row['userImage'] = base64_encode($row['userImage']);
        }
        $users[] = $row;
    }
    echo json_encode(['status' => 'success', 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
