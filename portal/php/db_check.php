<?php
header('Content-Type: application/json');
include 'config.php';

$output = [];

// 1. Which database are we connected to?
$r = $conn->query("SELECT DATABASE() AS db, USER() AS user, @@port AS port, @@socket AS socket");
$output['connection'] = $r->fetch_assoc();

// 2. Total rows in assign_room
$r = $conn->query("SELECT COUNT(*) AS total FROM assign_room");
$output['total_assign_room'] = (int)$r->fetch_assoc()['total'];

// 3. Rows with hostel_id=2, session_id=2 (what the page looks for)
$r = $conn->query("SELECT COUNT(*) AS cnt FROM assign_room WHERE hostel_id = 2 AND session_id = 2");
$output['hostel2_session2'] = (int)$r->fetch_assoc()['cnt'];

// 4. Rows with NULL hostel_id or session_id (old broken uploads)
$r = $conn->query("SELECT COUNT(*) AS cnt FROM assign_room WHERE hostel_id IS NULL OR session_id IS NULL");
$output['null_hostel_or_session'] = (int)$r->fetch_assoc()['cnt'];

// 5. Last 10 rows by highest sn
$r = $conn->query("SELECT sn, hostel_id, session_id, student_name, room_bunk, matric_no FROM assign_room ORDER BY sn DESC LIMIT 10");
$output['last_10_rows'] = $r->fetch_all(MYSQLI_ASSOC);

// 6. Distinct hostel_id + session_id combos
$r = $conn->query("SELECT hostel_id, session_id, COUNT(*) AS cnt FROM assign_room GROUP BY hostel_id, session_id ORDER BY cnt DESC");
$output['by_hostel_session'] = $r->fetch_all(MYSQLI_ASSOC);

// 7. Count rows with sn=0 (from old broken upload)
$r = $conn->query("SELECT COUNT(*) AS cnt FROM assign_room WHERE sn = 0");
$output['sn0_rows'] = (int)$r->fetch_assoc()['cnt'];

// 8. Show sample of sn=0 rows
$r = $conn->query("SELECT sn, hostel_id, session_id, student_name, room_bunk, matric_no FROM assign_room WHERE sn = 0 LIMIT 10");
$output['sn0_sample'] = $r->fetch_all(MYSQLI_ASSOC);

// 9. Check for ROOM 301 rows
$r = $conn->query("SELECT sn, hostel_id, session_id, student_name, room_bunk, matric_no FROM assign_room WHERE room_bunk LIKE 'ROOM 301%'");
$output['room301_rows'] = $r->fetch_all(MYSQLI_ASSOC);

// 10. Show all rows with student_name containing 'jess' (from CSV upload)
$r = $conn->query("SELECT sn, hostel_id, session_id, student_name, room_bunk, matric_no FROM assign_room WHERE student_name LIKE '%jess%' OR student_name LIKE '%okok%'");
$output['uploaded_student_rows'] = $r->fetch_all(MYSQLI_ASSOC);

$conn->close();
echo json_encode($output, JSON_PRETTY_PRINT);
?>
