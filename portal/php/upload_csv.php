have added pgh<?php
include 'auth_admin.php';
header('Content-Type: application/json');

include 'config.php';

require_once __DIR__ . '/academic_helper.php';

pt_require('assign_room');

function gethostelname($id) {
    $hostels = pt_all_hostels();
    foreach ($hostels as $h) {
        if ((int)$h['id'] === (int)$id) {
            return $h['name'];
        }
    }
    return 'Hostel #' . $id;
}

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
    $rowTrace = [];

    $conn->begin_transaction();

    // Get the current max SN once before the loop so each row gets a unique,
    // incrementing value.  (Inside a REPEATABLE READ transaction a plain
    // SELECT would return the same snapshot every time.)
    $snStmt = $conn->prepare("SELECT COALESCE(MAX(sn), 0) FROM assign_room");
    $snStmt->execute();
    $snResult = $snStmt->get_result();
    $currentMaxSn = (int)$snResult->fetch_row()[0];
    $snStmt->close();

    // Create the upload batch record first so every row imported by this file
    // can be tagged with it (and later revoked together if the upload was wrong).
    $adminId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $fileName = basename((string)$file['name']);
    $batchStmt = $conn->prepare("INSERT INTO upload_batch (hostel_id, session_id, file_name, total_rows, error_rows, uploaded_by, created_at) VALUES (?, ?, ?, 0, 0, ?, NOW())");
    if (!$batchStmt) {
        throw new Exception('Failed to prepare upload batch query: ' . $conn->error);
    }
    $batchStmt->bind_param('iisi', $hostelId, $sessionId, $fileName, $adminId);
    if (!$batchStmt->execute()) {
        $batchStmt->close();
        throw new Exception('Failed to create upload batch record: ' . $batchStmt->error);
    }
    $uploadBatchId = $batchStmt->insert_id;
    $batchStmt->close();

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

        $traceRow = [
            'csv_row' => $row,
            'parsed' => compact('student_name', 'matric_no', 'department', 'parent_number', 'level', 'student_number', 'room_bunk', 'bed_space'),
            'hostel_id' => $hostelId,
            'session_id' => $sessionId,
        ];

        // Note: CSV assignments are roster-based (from assign_room).
        // No room lookup / auto-creation here — the reservation stores the
        // room_bunk string directly. Portal-based assignments (assign_room.php)
        // are the only path that links a reservation to a real room record.

        // Guard: check whether this matric_no already exists in a DIFFERENT
        // hostel for the same session.  The UNIQUE KEY on (matric_no, session_id)
        // would silently overwrite the other hostel's row otherwise.
        // (Skip guard when matric_no is empty — room-only reservation.)
        if ($matric_no !== '') {
            $guardStmt = $conn->prepare("SELECT hostel_id FROM assign_room WHERE matric_no = ? AND session_id = ? LIMIT 1");
            if ($guardStmt) {
                $guardStmt->bind_param("si", $matric_no, $sessionId);
                $guardStmt->execute();
                $guardResult = $guardStmt->get_result();
                if ($guardResult && $guardResult->num_rows > 0) {
                    $existingRow = $guardResult->fetch_assoc();
                    $existingHostel = (int)$existingRow['hostel_id'];
                    if ($existingHostel !== (int)$hostelId) {
                        $existingName = gethostelname($existingHostel);
                        $errorCount++;
                        $traceRow['action'] = 'GUARD_SKIP';
                        $traceRow['reason'] = "$matric_no is already assigned to $existingName (hostel $existingHostel)";
                        $rowTrace[] = $traceRow;
                        $errors[] = "Row skipped: $matric_no is already assigned to $existingName (hostel $existingHostel) in this session. Remove from that hostel first before assigning here.";
                        $guardStmt->close();
                        continue;
                    }
                }
                $guardStmt->close();
            }
        }

        // Use NULL for empty fields so the unique key on (matric_no, session_id)
        // allows multiple room-only rows without conflicting.
        $matricSql = $matric_no !== '' ? $matric_no : null;
        $nameSql   = $student_name !== '' ? $student_name : null;
        $deptSql   = $department !== '' ? $department : null;
        $parentSql = $parent_number !== '' ? $parent_number : null;
        $levelSql  = $level !== '' ? $level : null;
        $snumSql   = $student_number !== '' ? $student_number : null;

        // Check if this room_bunk already exists for this hostel+session.
        // Room bunk is the unique identifier — update the existing row instead
        // of inserting a duplicate.
        $existStmt = $conn->prepare("SELECT sn FROM assign_room WHERE room_bunk = ? AND hostel_id = ? AND session_id = ? LIMIT 1");
        $existingSn = null;
        if ($existStmt) {
            $existStmt->bind_param("sii", $room_bunk, $hostelId, $sessionId);
            $existStmt->execute();
            $existResult = $existStmt->get_result();
            if ($existResult && $existResult->num_rows > 0) {
                $existingSn = (int)$existResult->fetch_assoc()['sn'];
            }
            $existStmt->close();
        }

        if ($existingSn !== null) {
            // UPDATE existing row — room_bunk already exists in this hostel+session
            $upsertStmt = $conn->prepare("
                UPDATE assign_room SET
                    student_name = ?,
                    matric_no = ?,
                    department = ?,
                    parent_number = ?,
                    level = ?,
                    student_number = ?,
                    bed_space = ?,
                    upload_batch_id = ?,
                    updated_at = NOW()
                WHERE room_bunk = ? AND hostel_id = ? AND session_id = ?
            ");
            if (!$upsertStmt) {
                $errorCount++;
                $errors[] = "Error preparing update for $room_bunk: " . $conn->error;
                continue;
            }
            $upsertStmt->bind_param("sssssssisis", $nameSql, $matricSql, $deptSql, $parentSql, $levelSql, $snumSql, $bed_space, $uploadBatchId, $room_bunk, $hostelId, $sessionId);
        } else {
            // INSERT new row — room_bunk does not exist yet
            $currentMaxSn++;
            $nextSn = $currentMaxSn;

            $upsertStmt = $conn->prepare("
                INSERT INTO assign_room 
                    (sn, hostel_id, student_name, matric_no, department, parent_number, level, student_number, room_bunk, bed_space, session_id, upload_batch_id, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            if (!$upsertStmt) {
                $errorCount++;
                $errors[] = "Error preparing insert for $room_bunk: " . $conn->error;
                continue;
            }
            $upsertStmt->bind_param("iissssssssii", $nextSn, $hostelId, $nameSql, $matricSql, $deptSql, $parentSql, $levelSql, $snumSql, $room_bunk, $bed_space, $sessionId, $uploadBatchId);
        }

        if (!$upsertStmt->execute()) {
            $errorCount++;
            $errors[] = "Error saving $room_bunk: " . $upsertStmt->error;
            $traceRow['action'] = 'FAILED';
            $traceRow['db_error'] = $upsertStmt->error;
            $rowTrace[] = $traceRow;
            $upsertStmt->close();
            continue;
        }
        $assign_room_id = ($existingSn !== null) ? $existingSn : $upsertStmt->insert_id;
        $traceRow['action'] = ($existingSn !== null) ? 'UPDATE' : 'INSERT';
        $traceRow['assign_room_id'] = $assign_room_id;
        $traceRow['existing_sn'] = $existingSn;
        $upsertStmt->close();

        // Find user by matric_no — skip reservation logic when matric_no is empty
        if ($matric_no === '') {
            // Room-only reservation (no student linked yet)
            $traceRow['reservation'] = 'room_only';
            $rowTrace[] = $traceRow;
            $importCount++;
        } else {
        $userStmt = $conn->prepare("SELECT id FROM userregistration WHERE regNo = ?");
        if (!$userStmt) {
            $errorCount++;
            $errors[] = "Error preparing user lookup for $matric_no: " . $conn->error;
            $importCount++;
            continue;
        }
        $userStmt->bind_param("s", $matric_no);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult && $userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            
            // Guard: check if this user already has a reservation in a
            // DIFFERENT hostel for the same session.
            $resGuard = $conn->prepare("SELECT hostel_id FROM reservations WHERE user_id = ? AND session_id = ? LIMIT 1");
            if ($resGuard) {
                $resGuard->bind_param("ii", $user['id'], $sessionId);
                $resGuard->execute();
                $rgRes = $resGuard->get_result();
                if ($rgRes && $rgRes->num_rows > 0) {
                    $rgRow = $rgRes->fetch_assoc();
                    $rgHostel = (int)$rgRow['hostel_id'];
                    if ($rgHostel !== (int)$hostelId) {
                        $rgName = gethostelname($rgHostel);
                        $errorCount++;
                        $traceRow['reservation'] = 'RES_GUARD_SKIP';
                        $traceRow['reason'] = "$matric_no already has reservation in $rgName (hostel $rgHostel)";
                        $rowTrace[] = $traceRow;
                        $errors[] = "Row skipped: $matric_no already has a reservation in $rgName (hostel $rgHostel) for this session. Remove from that hostel first.";
                        $resGuard->close();
                        $userStmt->close();
                        continue;
                    }
                }
                $resGuard->close();
            }

            // Upsert reservation (insert or update on duplicate user_id+session_id).
            // CSV-assigned students get room_bunk stored directly (no room_id link).
            // If a reservation already exists from a portal assignment (room_id set),
            // the room_bunk is still recorded but the room link is preserved.
            $resStmt = $conn->prepare("
                INSERT INTO reservations (user_id, session_id, hostel_id, room_bunk, bed_space, upload_batch_id, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    hostel_id = VALUES(hostel_id),
                    room_bunk = VALUES(room_bunk),
                    bed_space = VALUES(bed_space),
                    upload_batch_id = VALUES(upload_batch_id),
                    updated_at = NOW()
            ");
            if ($resStmt) {
                $resStmt->bind_param("iiissi", $user['id'], $sessionId, $hostelId, $room_bunk, $bed_space, $uploadBatchId);
                
                if (!$resStmt->execute()) {
                    $errorCount++;
                    $traceRow['reservation'] = 'FAILED';
                    $traceRow['res_error'] = $resStmt->error;
                    $rowTrace[] = $traceRow;
                    $errors[] = "Error upserting reservation for $matric_no: " . $resStmt->error;
                } else {
                    $traceRow['reservation'] = 'upserted';
                    $traceRow['user_id'] = $user['id'];
                    $rowTrace[] = $traceRow;
                    $importCount++;
                }
                $resStmt->close();
            } else {
                $traceRow['reservation'] = 'prepare_failed';
                $rowTrace[] = $traceRow;
                $importCount++;
            }
        } else {
            // Student not registered yet - still count assign_room as imported
            $traceRow['reservation'] = 'user_not_found';
            $rowTrace[] = $traceRow;
            $importCount++;
        }
        $userStmt->close();
        }
    }

    fclose($csvFile);

    // Finalize the batch record with the actual import/error counts.
    $finBatch = $conn->prepare("UPDATE upload_batch SET total_rows = ?, error_rows = ? WHERE id = ?");
    $finBatch->bind_param('iii', $importCount, $errorCount, $uploadBatchId);
    $finBatch->execute();
    $finBatch->close();

    if ($importCount > 0) {
        if ($conn->commit()) {
            // Verify the rows actually persisted after commit
            $verify = $conn->prepare("SELECT COUNT(*) AS cnt FROM assign_room WHERE hostel_id = ? AND session_id = ?");
            $verify->bind_param('ii', $hostelId, $sessionId);
            $verify->execute();
            $vRow = $verify->get_result()->fetch_assoc();
            $verify->close();
            $actualCount = (int)$vRow['cnt'];

            $response['status'] = 'success';
            $response['message'] = "$importCount records imported successfully for hostel $hostelId.";
            $response['batch_id'] = $uploadBatchId;
            $response['_debug'] = [
                'hostel_id' => $hostelId,
                'session_id' => $sessionId,
                'import_count' => $importCount,
                'error_count' => $errorCount,
                'errors' => $errors,
                'db_rows_after_commit' => $actualCount,
                'conn_error' => $conn->error ?: 'none',
                'row_trace' => $rowTrace,
            ];
        } else {
            $conn->rollback();
            $response['status'] = 'error';
            $response['message'] = 'Failed to save records: ' . $conn->error;
            if (!empty($errors)) {
                $response['errors'] = $errors;
            }
        }
    } else {
        $conn->rollback();
        if ($errorCount > 0 && !empty($errors)) {
            $response['status'] = 'error';
            $response['message'] = "No records were imported. $errorCount row(s) were skipped. See details below.";
            $response['errors'] = $errors;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'No records were imported. Please check your CSV file. The file must contain the 8 required columns: Student Name, Matric No, Department, Parent Number, Level, Student Number, Room Bunk, Bed Space.';
        }
    }

} catch (\Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

if (ob_get_length()) { ob_end_clean(); }
header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
?>