<?php
include 'config.php';

try {
    $stmt = $conn->prepare("SELECT id, regNo, firstName, middleName, lastName, gender, contactNo, email FROM userregistration");
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
