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

    // Get session (defaults to active academic session)
    $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : pt_active_session_id();
    if ($sessionId <= 0) {
        throw new Exception('No academic session selected. Please choose a session.');
    }
    $sessionCheck = $conn->prepare("SELECT id FROM academic_session WHERE id = ?");
    $sessionCheck->bind_param('i', $sessionId);
    $sessionCheck->execute();
    $sessionCheck->store_result();
    if ($sessionCheck->num_rows === 0) {
        $sessionCheck->close();
        throw new Exception('Selected academic session does not exist.');
    }
    $sessionCheck->close();

    // Get hostel_id from POST data (must be a real hostel, not 0/"All")
    $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0;
    if ($hostelId <= 0) {
        throw new Exception('Please select a hostel to import the assignment for.');
    }
    $hostelCheck = $conn->prepare("SELECT id FROM hostel WHERE id = ?");
    $hostelCheck->bind_param('i', $hostelId);
    $hostelCheck->execute();
    $hostelCheck->store_result();
    if ($hostelCheck->num_rows === 0) {
        $hostelCheck->close();
        throw new Exception('Selected hostel does not exist.');
    }
    $hostelCheck->close();

    $csvFile = fopen($file['tmp_name'], 'r');
    $header = fgetcsv($csvFile); // skip header

    // Map columns by header name so the template round-trips cleanly. Supports
    // both the Download CSV columns (10 cols: Serial Number, Student Name,
    // Matric No, Department, Parent Number, Level, Student Number, Room Bunk,
    // Bed Space, Hostel) and the older snake_case template (8 cols).
    $colMap = array();
    if (is_array($header)) {
        foreach ($header as $i => $h) {
            $norm = strtolower(trim((string)$h));
            $norm = preg_replace('/[\s_]+/', '', $norm);
            $norm = str_replace('-', '', $norm);
            switch ($norm) {
                case 'studentname':
                case 'studentname2':
                    $colMap['student_name'] = $i;
                    break;
                case 'matricno':
                    $colMap['matric_no'] = $i;
                    break;
                case 'department':
                    $colMap['department'] = $i;
                    break;
                case 'parentnumber':
                    $colMap['parent_number'] = $i;
                    break;
                case 'level':
                    $colMap['level'] = $i;
                    break;
                case 'studentnumber':
                    $colMap['student_number'] = $i;
                    break;
                case 'roombunk':
                case 'room':
                    $colMap['room_bunk'] = $i;
                    break;
                case 'bedspace':
                case 'bunkspace':
                    $colMap['bed_space'] = $i;
                    break;
                case 'serialnumber':
                case 'hostel':
                    $colMap[$norm] = $i; // informational columns, ignored on import
                    break;
            }
        }
    }
    $hasNamedCols = isset($colMap['student_name']) || isset($colMap['matric_no']);

    $importCount = 0;
    $errorCount = 0;
    $errors = [];

    $conn->begin_transaction();

    while (($row = fgetcsv($csvFile)) !== FALSE) {
        if ($hasNamedCols) {
            $get = function ($key) use ($row, $colMap) {
                return isset($colMap[$key]) && isset($row[$colMap[$key]]) ? $row[$colMap[$key]] : '';
            };
            $student_name = trim((string)$get('student_name'));
            $matric_no = trim((string)$get('matric_no'));
            $department = trim((string)$get('department'));
            $parent_number = trim((string)$get('parent_number'));
            $level = trim((string)$get('level'));
            $student_number = trim((string)$get('student_number'));
            $room_bunk = trim((string)$get('room_bunk'));
            $bed_space = trim((string)$get('bed_space'));
        } else {
            if (count($row) < 8) {
                $errorCount++;
                $errors[] = "Row skipped: Not enough columns (expected: student_name, matric_no, department, parent_number, level, student_number, room_bunk, bed_space)";
                continue;
            }
            $student_name = trim($row[0]);
            $matric_no = trim($row[1]);
            $department = trim($row[2]);
            $parent_number = trim($row[3]);
            $level = trim($row[4]);
            $student_number = trim($row[5]);
            $room_bunk = trim($row[6]);
            $bed_space = trim($row[7]);
        }

        // Derive the bed space from the room_bunk tail when the CSV doesn't
        // provide one (e.g. "ROOM 323-2U" -> "Bunk 2 Up").
        if ($bed_space === '' && $room_bunk !== '') {
            $bed_space = pt_derive_bed_space($room_bunk);
        }

        if (!$room_bunk) {
            $errorCount++;
            $errors[] = "Row skipped: Room bunk is required";
            continue;
        }

        if (!$matric_no) {
            $errorCount++;
            $errors[] = "Row skipped: Matric number is required";
            continue;
        }

        // Note: CSV assignments are roster-based (from assign_room).
        // No room lookup / auto-creation here — the reservation stores the
        // room_bunk string directly. Portal-based assignments (assign_room.php)
        // are the only path that links a reservation to a real room record.

        // Upsert assign_room record (insert or update on duplicate matric_no+session_id or room_bunk+session_id)
        // Get next sn value
        $snStmt = $conn->prepare("SELECT COALESCE(MAX(sn), 0) + 1 FROM assign_room");
        $snStmt->execute();
        $snResult = $snStmt->get_result();
        $nextSn = $snResult->fetch_row()[0];
        $snStmt->close();

        $upsertStmt = $conn->prepare("
            INSERT INTO assign_room 
                (sn, hostel_id, student_name, matric_no, department, parent_number, level, student_number, room_bunk, bed_space, session_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                hostel_id = VALUES(hostel_id),
                student_name = VALUES(student_name),
                department = VALUES(department),
                parent_number = VALUES(parent_number),
                level = VALUES(level),
                student_number = VALUES(student_number),
                bed_space = VALUES(bed_space),
                updated_at = NOW()
        ");
        $upsertStmt->bind_param("iissssssssi", $nextSn, $hostelId, $student_name, $matric_no, $department, $parent_number, $level, $student_number, $room_bunk, $bed_space, $sessionId);
        
        if (!$upsertStmt->execute()) {
            $errorCount++;
            $errors[] = "Error upserting assign_room for $matric_no: " . $upsertStmt->error;
            $upsertStmt->close();
            continue;
        }
        $assign_room_id = $upsertStmt->insert_id;
        $upsertStmt->close();

        // Find user by matric_no
        $userStmt = $conn->prepare("SELECT id FROM userregistration WHERE regNo = ?");
        $userStmt->bind_param("s", $matric_no);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult && $userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            
            // Upsert reservation (insert or update on duplicate user_id+session_id).
            // CSV-assigned students get room_bunk stored directly (no room_id link).
            // If a reservation already exists from a portal assignment (room_id set),
            // the room_bunk is still recorded but the room link is preserved.
            $resStmt = $conn->prepare("
                INSERT INTO reservations (user_id, session_id, hostel_id, room_bunk, bed_space, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    hostel_id = VALUES(hostel_id),
                    room_bunk = VALUES(room_bunk),
                    bed_space = VALUES(bed_space),
                    updated_at = NOW()
            ");
            $resStmt->bind_param("iiiss", $user['id'], $sessionId, $hostelId, $room_bunk, $bed_space);
            
            if (!$resStmt->execute()) {
                $errorCount++;
                $errors[] = "Error upserting reservation for $matric_no: " . $resStmt->error;
            } else {
                $importCount++;
            }
            $resStmt->close();
        } else {
            // Student not registered yet - still count assign_room as imported
            $importCount++;
        }
        $userStmt->close();
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