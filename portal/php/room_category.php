<?php
include 'auth_admin.php';
include 'config.php';

$hostelId = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 1;

$sql = "SELECT id, hostel_id, room_type, rate FROM room_category WHERE hostel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $hostelId);
$stmt->execute();
$result = $stmt->get_result();

$categories = array();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

header('Content-Type: application/json');
echo json_encode($categories);

$stmt->close();
$conn->close();
?>
