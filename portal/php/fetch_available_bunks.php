<?php
include 'config.php';

$hostel_id = isset($_GET['hostel_id']) ? (int)$_GET['hostel_id'] : 0;
$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;

if ($hostel_id <= 0 || $session_id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, room_bunk FROM assign_room WHERE hostel_id = ? AND session_id = ? AND student_name IS NULL AND matric_no IS NULL AND student_number IS NULL ORDER BY room_bunk ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $hostel_id, $session_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode($data);
?>
