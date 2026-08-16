<?php
include 'auth_admin.php';
header('Content-Type: application/json');

include 'config.php';

require_once __DIR__ . '/academic_helper.php';

pt_require('assign_room');

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
        throw new Exception('Error uploading file. Please try again.');
    }

    $file = $_FILES['csv_file'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext !== 'csv') {
        throw new Exception('Only CSV files are allowed.');
    }

    // Get active session
    $sessionId = pt_active_session_id();
    if (!$sessionId) {
        throw new Exception('No active academic session. Cannot import assignments.');
    }

    // Get hostel_id from POST data (default to 1)
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 1;

    $csvFile = fopen($file['tmp_name'], 'r');
    fgetcsv($csvFile); // skip header

    $importCount = 0;
    $errorCount = 0;
    $errors = [];

    $conn->begin_transaction();

    while (($row = fgetcsv($csvFile)) !== FALSE) {
        if (count($row) < 7) {
            $errorCount++;
            $errors[] = "Row skipped: Not enough columns (expected 7)";
            continue;
        }

        $student_name = trim($row[0]);
        $matric_no = trim($row[1]);
        $department = trim($row[2]);
        $parent_number = trim($row[3]);
        $level = trim($row[4]);
        $student_number = trim($row[5]);
        $room_bunk = trim($row[6]);

        if (!$room_bunk) {
            $errorCount++;
            $errors[] = "Row skipped: Room bunk is required";
            continue;
        }

        // Verify room exists in this hostel
        $roomStmt = $conn->prepare("SELECT id, category_id FROM room WHERE room_number = ? AND hostel_id = ?");
        $roomStmt->bind_param("si", $room_bunk, $hostelId);
        $roomStmt->execute();
        $roomResult = $roomStmt->get_result();
        
        if (!$roomResult || $roomResult->num_rows === 0) {
            $errorCount++;
            $errors[] = "Room '$room_bunk' not found in selected hostel";
            $roomStmt->close();
            continue;
        }
        $room = $roomResult->fetch_assoc();
        $roomStmt->close();

        // Check if assign_room record exists for this room_bunk in active session AND hostel
        $checkStmt = $conn->prepare("SELECT sn FROM assign_room WHERE room_bunk = ? AND session_id = ?");
        $checkStmt->bind_param("si", $room_bunk, $sessionId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing
            $updateStmt = $conn->prepare("UPDATE assign_room SET 
                student_name = ?, matric_no = ?, department = ?, parent_number = ?, level = ?, student_number = ? 
                WHERE room_bunk = ? AND session_id = ?");
            $updateStmt->bind_param("sssssssi", $student_name, $matric_no, $department, $parent_number, $level, $student_number, $room_bunk, $sessionId);
            
            if ($updateStmt->execute()) {
                $importCount++;
            } else {
                $errorCount++;
                $errors[] = "Error updating $room_bunk: " . $updateStmt->error;
            }
            $updateStmt->close();
        } else {
            // Insert new assign_room record
            $insertStmt = $conn->prepare("INSERT INTO assign_room 
                (student_name, matric_no, department, parent_number, level, student_number, room_bunk, session_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssssssi", $student_name, $matric_no, $department, $parent_number, $level, $student_number, $room_bunk, $sessionId);
            
            if ($insertStmt->execute()) {
                $importCount++;
                
                // Create reservation
                $userStmt = $conn->prepare("SELECT id FROM userregistration WHERE regNo = ?");
                $userStmt->bind_param("s", $matric_no);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                if ($userResult && $userResult->num_rows > 0) {
                    $user = $userResult->fetch_assoc();
                    $resStmt = $conn->prepare("INSERT INTO reservations (user_id, room_id, room_category, session_id, bed_space) VALUES (?, ?, ?, ?, 1)");
                    $resStmt->bind_param("iiii", $user['id'], $room['id'], $room['category_id'], $sessionId);
                    $resStmt->execute();
                    $resStmt->close();
                }
                $userStmt->close();
            } else {
                $errorCount++;
                $errors[] = "Error inserting $room_bunk: " . $insertStmt->error;
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    }

    fclose($csvFile);
    $conn->commit();

    if ($importCount > 0) {
        $response['status'] = 'success';
        $response['message'] = "$importCount records imported successfully for hostel $hostelId.";
        if ($errorCount > 0) {
            $response['message'] .= " $errorCount records failed.";
            $response['errors'] = $errors;
        }
    } else {
        throw new Exception('No records were imported. Please check your CSV file.');
    }

} catch (Exception $e) {
    if ($conn && $conn->errno === 0) { $conn->rollback(); }
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>