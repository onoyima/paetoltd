<?php
include 'auth_admin.php';
include 'config.php';

$hostelId = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 1;

$sql = "SELECT id, hostel_id, room_number, category_id, full_capacity, available_space, status FROM room WHERE hostel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $hostelId);
$stmt->execute();
$result = $stmt->get_result();

$rooms = array();
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

header('Content-Type: application/json');
echo json_encode($rooms);

$stmt->close();
$conn->close();
?>
