<?php
include 'php/config.php'; // Include your database connection file

$categoryId = $_GET['category_id'];

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
