<?php
include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT COUNT(*) as total FROM assign_room";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
echo "Total rows in assign_room: " . $row['total'];

$sql2 = "SELECT * FROM assign_room LIMIT 5";
$result2 = $conn->query($sql2);
if ($result2->num_rows > 0) {
    while($row2 = $result2->fetch_assoc()) {
        echo "<br>Row: " . print_r($row2, true);
    }
} else {
    echo "<br>No rows found";
}

$conn->close();
?>