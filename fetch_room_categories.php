<?php
include 'php/config.php'; // Include your database connection file

$sql = "SELECT id, room_type FROM room_category";
$result = $conn->query($sql);

$categories = array();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

header('Content-Type: application/json');
echo json_encode($categories);

$conn->close();
?>
