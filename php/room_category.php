<?php
include 'config.php'; // Include your database connection file

$sql = "SELECT id, room_type, rate FROM room_category";
$result = $conn->query($sql);

$categories = array();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

header('Content-Type: application/json');
echo json_encode($categories);

$conn->close();
?>
