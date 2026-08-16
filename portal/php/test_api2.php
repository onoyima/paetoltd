<?php
// Temporary test - bypass session check
include 'config.php';

$response = ['status' => 'error', 'message' => 'Unknown error', 'users' => []];

// Bypass the auth gate for testing
$tableCheck = $conn->query("SHOW TABLES LIKE 'assign_room'");
if ($tableCheck->num_rows == 0) {
    $response['message'] = 'Table assign_room does not exist';
    echo json_encode($response);
    exit;
}

$sql = "SELECT sn, student_name, matric_no, department, parent_number, level, student_number, room_bunk FROM assign_room";
$result = $conn->query($sql);

if (!$result) {
    $response['message'] = 'Query failed: ' . $conn->error;
    echo json_encode($response);
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'sn' => $row['sn'],
        'student_name' => $row['student_name'],
        'matric_no' => $row['matric_no'],
        'department' => $row['department'],
        'parent_number' => $row['parent_number'],
        'level' => $row['level'],
        'student_number' => $row['student_number'],
        'room_bunk' => $row['room_bunk']
    ];
}

$response['status'] = 'success';
$response['message'] = 'Data fetched successfully';
$response['users'] = $users;

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
$conn->close();
?>