<?php
include 'auth_admin.php';
include 'config.php';

$hostelId = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 1;

if ($hostelId > 0) {
    $sql = "SELECT r.id, r.hostel_id, h.name AS hostel_name, r.room_number, r.category_id, r.full_capacity, r.available_space, r.status
            FROM room r
            LEFT JOIN hostel h ON h.id = r.hostel_id
            WHERE r.hostel_id = ?
            ORDER BY r.id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $hostelId);
} else {
    $sql = "SELECT r.id, r.hostel_id, h.name AS hostel_name, r.room_number, r.category_id, r.full_capacity, r.available_space, r.status
            FROM room r
            LEFT JOIN hostel h ON h.id = r.hostel_id
            ORDER BY r.hostel_id ASC, r.id ASC";
    $stmt = $conn->prepare($sql);
}
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
