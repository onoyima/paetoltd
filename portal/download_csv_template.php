<?php
require_once __DIR__ . '/php/rbac.php';
session_start();
pt_require('assign_room');

include 'php/config.php';
require_once __DIR__ . '/php/academic_helper.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="room_assignment_template_hostel_' . (isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 1) . '.csv"');

$hostelId = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 1;

// Get the hostel name so the template mirrors the Download CSV output exactly.
$hostelName = '';
$hostelStmt = $conn->prepare("SELECT name FROM hostel WHERE id = ?");
$hostelStmt->bind_param('i', $hostelId);
$hostelStmt->execute();
$hostelResult = $hostelStmt->get_result();
if ($hostelRow = $hostelResult->fetch_assoc()) {
    $hostelName = $hostelRow['name'];
}
$hostelStmt->close();

// Get existing rooms for this hostel
$stmt = $conn->prepare("SELECT room_number FROM room WHERE hostel_id = ? ORDER BY room_number");
$stmt->bind_param('i', $hostelId);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row['room_number'];
}
$stmt->close();

$output = fopen('php://output', 'w');

// Header row — must match the Download CSV column set on assigned_room.php
fputcsv($output, [
    'Serial Number',
    'Student Name',
    'Matric No',
    'Department',
    'Parent Number',
    'Level',
    'Student Number',
    'Room Bunk',
    'Bed Space',
    'Hostel'
]);

// Example rows with existing rooms (first 5)
$exampleStudents = [
    ['1', 'John Doe', 'VUG/ACC/22/1234', 'ACCOUNTING', '08012345678', '400', '7061234567', $rooms[0] ?? 'ROOM 101', 'A1', $hostelName],
    ['2', 'Jane Smith', 'VUG/LAW/22/5678', 'LAW', '08087654321', '300', '7067654321', $rooms[1] ?? 'ROOM 102', 'B2', $hostelName],
    ['3', 'Bob Wilson', 'VUG/CSC/23/9012', 'COMPUTER SCIENCE', '08011223344', '200', '7061122334', $rooms[2] ?? 'ROOM 103', 'C1', $hostelName],
    ['4', 'Alice Brown', 'VUG/BNS/24/3456', 'NURSING SCIENCE', '08055667788', '100', '7065566778', $rooms[3] ?? 'ROOM 104', 'D2', $hostelName],
    ['5', 'Charlie Davis', 'VUG/MAC/22/7890', 'MASS COMM.', '08099887766', '400', '7069988776', $rooms[4] ?? 'ROOM 105', 'A2', $hostelName],
];

foreach ($exampleStudents as $row) {
    fputcsv($output, $row);
}

// Add all existing rooms as empty template rows
foreach ($rooms as $room) {
    fputcsv($output, ['', '', '', '', '', '', '', $room, '', '']);
}

fclose($output);
$conn->close();
exit;
