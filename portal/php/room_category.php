<?php
include 'auth_admin.php';
include 'config.php';

$hostelId = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 1;

if ($hostelId > 0) {
    $sql = "SELECT c.id, c.hostel_id, h.name AS hostel_name, c.room_type, c.rate
            FROM room_category c
            LEFT JOIN hostel h ON h.id = c.hostel_id
            WHERE c.hostel_id = ?
            ORDER BY c.id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $hostelId);
} else {
    $sql = "SELECT c.id, c.hostel_id, h.name AS hostel_name, c.room_type, c.rate
            FROM room_category c
            LEFT JOIN hostel h ON h.id = c.hostel_id
            ORDER BY c.hostel_id ASC, c.id ASC";
    $stmt = $conn->prepare($sql);
}
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
