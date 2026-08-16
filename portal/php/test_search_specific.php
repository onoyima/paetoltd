<?php
require_once 'config.php';

// Test with a known matric number from the sample data
$testMatric = 'VUG/MAC/23/9337';
$testPhone = '08089603924';
$testParent = '08101012201';

echo "Testing search with known data:\n";
echo "Test Matric: $testMatric\n";
echo "Test Phone: $testPhone\n";
echo "Test Parent: $testParent\n\n";

// Test the exact query from search_room_allocation.php
$sql = "SELECT 
            ar.id,
            ar.student_name,
            ar.matric_no,
            ar.department,
            ar.level,
            ar.student_number,
            ar.parent_number,
            ar.room_bunk,
            ar.created_at
        FROM assign_room ar 
        WHERE (ar.matric_no = ? OR ar.student_number = ? OR ar.parent_number = ?) 
        AND ar.matric_no IS NOT NULL 
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Prepare failed: " . $conn->error . "\n";
    exit;
}

// Test with matric number
echo "\n=== Testing with matric number ===\n";
$stmt->bind_param('sss', $testMatric, $testMatric, $testMatric);
if (!$stmt->execute()) {
    echo "Execute failed: " . $stmt->error . "\n";
} else {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo "FOUND: " . print_r($student, true);
    } else {
        echo "NOT FOUND with matric: $testMatric\n";
    }
}

// Test with phone number
echo "\n=== Testing with phone number ===\n";
$stmt->bind_param('sss', $testPhone, $testPhone, $testPhone);
if (!$stmt->execute()) {
    echo "Execute failed: " . $stmt->error . "\n";
} else {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo "FOUND: " . print_r($student, true);
    } else {
        echo "NOT FOUND with phone: $testPhone\n";
    }
}

// Test with parent number
echo "\n=== Testing with parent number ===\n";
$stmt->bind_param('sss', $testParent, $testParent, $testParent);
if (!$stmt->execute()) {
    echo "Execute failed: " . $stmt->error . "\n";
} else {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo "FOUND: " . print_r($student, true);
    } else {
        echo "NOT FOUND with parent: $testParent\n";
    }
}

$stmt->close();
$conn->close();
?>