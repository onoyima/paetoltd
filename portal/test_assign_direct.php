<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'php/config.php';
$userId = 653; // From payment 685
$assignRoomId = 1; // Assuming there is a bed space 1

$userQ = $conn->prepare("SELECT firstName, middleName, lastName, regNo, department, parentPhone, level, contactNo FROM userregistration WHERE id = ?");
$userQ->bind_param('i', $userId);
$userQ->execute();
$res = $userQ->get_result();
if ($res->num_rows == 0) {
    die("User not found");
}
$user = $res->fetch_assoc();
$userQ->close();

$student_name = trim($user['firstName'] . ' ' . $user['middleName'] . ' ' . $user['lastName']);
$matric_no = $user['regNo'];
$department = $user['department'];
$parent_number = $user['parentPhone'];
$level = $user['level'];
$student_number = $user['contactNo'];

$stmt = $conn->prepare("UPDATE assign_room SET student_name = ?, matric_no = ?, department = ?, parent_number = ?, level = ?, student_number = ?, updated_at = NOW() WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('ssssssi', $student_name, $matric_no, $department, $parent_number, $level, $student_number, $assignRoomId);
if ($stmt->execute()) {
    echo "Success!";
} else {
    echo "Execute failed: " . $stmt->error;
}
?>
