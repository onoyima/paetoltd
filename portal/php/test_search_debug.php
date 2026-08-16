<?php
require_once 'config.php';

echo "Testing assign_room table data:\n\n";

// Check table structure
$result = $conn->query("DESCRIBE assign_room");
echo "Table structure:\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n\nSample data (first 5 rows):\n";
// Check sample data
$result = $conn->query("SELECT matric_no, student_name, student_number, parent_number, room_bunk FROM assign_room LIMIT 5");
while($row = $result->fetch_assoc()) {
    echo "Matric: " . ($row['matric_no'] ?? 'NULL') . 
         ", Name: " . ($row['student_name'] ?? 'NULL') . 
         ", Student#: " . ($row['student_number'] ?? 'NULL') . 
         ", Parent#: " . ($row['parent_number'] ?? 'NULL') . 
         ", Room: " . ($row['room_bunk'] ?? 'NULL') . "\n";
}

echo "\n\nTotal records with matric_no not null:\n";
$result = $conn->query("SELECT COUNT(*) as count FROM assign_room WHERE matric_no IS NOT NULL AND matric_no != ''");
$row = $result->fetch_assoc();
echo "Count: " . $row['count'] . "\n";

$conn->close();
?>