<?php
include 'php/auth_admin.php'; // Admin-only gate
include 'php/config.php'; // Include your database connection file

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$sql = "SELECT id, room_number, available_space FROM room WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryId);
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
