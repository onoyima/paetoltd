<?php
include 'config.php'; // Include your database connection file

$sql = "SELECT id, room_number, category_id, full_capacity, available_space FROM room";
$result = $conn->query($sql);

$rooms = array();
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

header('Content-Type: application/json');
echo json_encode($rooms);

$conn->close();
?>
