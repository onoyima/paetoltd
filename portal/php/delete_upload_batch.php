<?php
// php/delete_upload_batch.php — revoke a CSV upload batch. Removes the batch
// record plus every assign_room and reservation row that the upload created
// (tagged with upload_batch_id), so the file can be re-uploaded after a fix.
//
// batch_id = 0 deletes the "legacy" data for the given hostel + session:
// assign_room rows imported before batch tracking (upload_batch_id IS NULL)
// and the CSV-created reservations (room_id IS NULL) for those students.
include 'auth_admin.php';
header('Content-Type: application/json');
include 'config.php';
require_once __DIR__ . '/academic_helper.php';

pt_require('assign_room');

$response = ['status' => 'error', 'message' => ''];

try {
    $batchId = isset($_POST['batch_id']) ? intval($_POST['batch_id']) : 0;

    if ($batchId === 0) {
        // ---- Legacy data deletion (untagged rows for a hostel + session) ----
        $hostelId = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0;
        $sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
        if ($hostelId <= 0 || $sessionId <= 0) {
            throw new Exception('Hostel and session are required for deleting earlier uploads.');
        }

        $conn->begin_transaction();

        // Reservations created from those CSV uploads (no linked room record).
        $resDel = $conn->prepare("
            DELETE r FROM reservations r
            INNER JOIN userregistration ur ON ur.id = r.user_id
            INNER JOIN assign_room ar ON ar.matric_no = ur.regNo
            WHERE ar.hostel_id = ? AND ar.session_id = ? AND ar.upload_batch_id IS NULL
              AND r.session_id = ? AND r.room_id IS NULL");
        $resDel->bind_param('iii', $hostelId, $sessionId, $sessionId);
        $resDel->execute();
        $deletedReservations = $resDel->affected_rows;
        $resDel->close();

        $arDel = $conn->prepare("DELETE FROM assign_room WHERE hostel_id = ? AND session_id = ? AND upload_batch_id IS NULL");
        $arDel->bind_param('ii', $hostelId, $sessionId);
        $arDel->execute();
        $deletedAssignments = $arDel->affected_rows;
        $arDel->close();

        $conn->commit();

        $response['status'] = 'success';
        $response['message'] = "Deleted earlier uploads for this hostel and session: $deletedAssignments assignments and $deletedReservations reservations removed.";
        $response['deleted_assignments'] = $deletedAssignments;
        $response['deleted_reservations'] = $deletedReservations;
        $response['legacy'] = true;
    } else {
        // ---- Tracked batch deletion ----
        $stmt = $conn->prepare("SELECT id, hostel_id, session_id, file_name FROM upload_batch WHERE id = ?");
        $stmt->bind_param('i', $batchId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            throw new Exception('Upload batch not found.');
        }
        $batch = $res->fetch_assoc();
        $stmt->close();

        $conn->begin_transaction();

        $delRes = $conn->prepare("DELETE FROM reservations WHERE upload_batch_id = ?");
        $delRes->bind_param('i', $batchId);
        $delRes->execute();
        $deletedReservations = $delRes->affected_rows;
        $delRes->close();

        $delAr = $conn->prepare("DELETE FROM assign_room WHERE upload_batch_id = ?");
        $delAr->bind_param('i', $batchId);
        $delAr->execute();
        $deletedAssignments = $delAr->affected_rows;
        $delAr->close();

        $delBatch = $conn->prepare("DELETE FROM upload_batch WHERE id = ?");
        $delBatch->bind_param('i', $batchId);
        $delBatch->execute();
        $delBatch->close();

        $conn->commit();

        $response['status'] = 'success';
        $response['message'] = "Deleted upload " . ($batch['file_name'] ? "\"{$batch['file_name']}\" " : '') . ": $deletedAssignments assignments and $deletedReservations reservations removed.";
        $response['deleted_assignments'] = $deletedAssignments;
        $response['deleted_reservations'] = $deletedReservations;
        $response['legacy'] = false;
    }

} catch (Exception $e) {
    if ($conn && $conn->errno === 0) { $conn->rollback(); }
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
