<?php
// php/fetch_upload_batches.php — list CSV upload batches for the Manage
// Uploads tab. Filtered by session + hostel. Also returns how many rows are
// "legacy" (imported before batch tracking, upload_batch_id IS NULL) so the UI
// can offer deleting them as a group too.
include 'auth_admin.php';
header('Content-Type: application/json');
include 'config.php';
require_once __DIR__ . '/academic_helper.php';

pt_require('assign_room');

$response = ['status' => 'error', 'message' => '', 'batches' => [], 'legacy' => ['rows' => 0]];

try {
    $sessionId = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
    $hostelId = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : 0;

    if ($sessionId <= 0 || $hostelId <= 0) {
        throw new Exception('Please choose a session and a hostel.');
    }

    // Tracked uploads (batches) for this hostel + session
    $sql = "SELECT b.id, b.hostel_id, b.session_id, b.file_name, b.total_rows, b.error_rows, b.uploaded_by, b.created_at,
                   a.username AS uploaded_by_name
            FROM upload_batch b
            LEFT JOIN admin a ON a.id = b.uploaded_by
            WHERE b.hostel_id = ? AND b.session_id = ?
            ORDER BY b.created_at DESC, b.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $hostelId, $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();

    $batches = [];
    while ($row = $result->fetch_assoc()) {
        $batches[] = [
            'id' => (int)$row['id'],
            'hostel_id' => (int)$row['hostel_id'],
            'session_id' => (int)$row['session_id'],
            'file_name' => $row['file_name'],
            'total_rows' => (int)$row['total_rows'],
            'error_rows' => (int)$row['error_rows'],
            'uploaded_by' => (int)$row['uploaded_by'],
            'uploaded_by_name' => $row['uploaded_by_name'],
            'created_at' => $row['created_at'],
            'legacy' => false
        ];
    }
    $stmt->close();

    // Legacy rows: imported before batch tracking existed.
    $legacyStmt = $conn->prepare("SELECT COUNT(*) c FROM assign_room WHERE hostel_id = ? AND session_id = ? AND upload_batch_id IS NULL");
    $legacyStmt->bind_param('ii', $hostelId, $sessionId);
    $legacyStmt->execute();
    $legacyRow = $legacyStmt->get_result()->fetch_assoc();
    $legacyStmt->close();
    $legacyCount = (int)$legacyRow['c'];

    if ($legacyCount > 0) {
        $batches[] = [
            'id' => 0,
            'hostel_id' => (int)$hostelId,
            'session_id' => (int)$sessionId,
            'file_name' => null,
            'total_rows' => $legacyCount,
            'error_rows' => 0,
            'uploaded_by' => null,
            'uploaded_by_name' => null,
            'created_at' => null,
            'legacy' => true
        ];
        $response['legacy']['rows'] = $legacyCount;
    }

    $response['status'] = 'success';
    $response['batches'] = $batches;
    $response['session_id'] = $sessionId;
    $response['hostel_id'] = $hostelId;

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
