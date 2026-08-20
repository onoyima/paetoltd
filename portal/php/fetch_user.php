<?php
include 'auth_admin.php';
include 'config.php';

try {
    // NOTE: userImage (BLOB) is intentionally excluded here — sending base64-encoded
    // images for every student in the list makes the response huge and very slow.
    // Images are only loaded when editing a specific student (separate request).
    $stmt = $conn->prepare("SELECT id, regNo, firstName, middleName, lastName, gender, department, level, contactNo, parentPhone, email FROM userregistration WHERE (firstName IS NOT NULL AND firstName != '') OR (lastName IS NOT NULL AND lastName != '') ORDER BY regNo ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(['status' => 'success', 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
