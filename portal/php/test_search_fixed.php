<?php
require_once 'config.php';

// Test with a known matric number from the sample data
$testMatric = 'VUG/MAC/23/9337';

echo "Testing search with corrected query:\n";
echo "Test Matric: $testMatric\n\n";

// Test the corrected query
$sql = "SELECT 
            ar.id,
            ar.student_name,
            ar.matric_no,
            ar.department,
            ar.level,
            ar.student_number,
            ar.parent_number,
            ar.room_bunk
        FROM assign_room ar 
        WHERE (ar.matric_no = ? OR ar.student_number = ? OR ar.parent_number = ?) 
        AND ar.matric_no IS NOT NULL 
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Prepare failed: " . $conn->error . "\n";
    exit;
}

$stmt->bind_param('sss', $testMatric, $testMatric, $testMatric);
if (!$stmt->execute()) {
    echo "Execute failed: " . $stmt->error . "\n";
} else {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo "SUCCESS! Found student:\n";
        echo "Name: " . $student['student_name'] . "\n";
        echo "Matric: " . $student['matric_no'] . "\n";
        echo "Department: " . $student['department'] . "\n";
        echo "Level: " . $student['level'] . "\n";
        echo "Student Phone: " . $student['student_number'] . "\n";
        echo "Parent Phone: " . $student['parent_number'] . "\n";
        echo "Room: " . $student['room_bunk'] . "\n";
    } else {
        echo "NOT FOUND with matric: $testMatric\n";
    }
}

$stmt->close();
$conn->close();
?>