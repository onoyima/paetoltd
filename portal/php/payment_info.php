<?php
session_start(); // Resume session

// Ensure user is logged in and session is active
if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Session expired. Please log in again.'));
    exit;
}

$_SESSION['timeout'] = time() + 1800;

// Database connection
include 'config.php';
require_once __DIR__ . '/academic_helper.php';

// Initialize response array
$response = array();

// Only allow payment uploads while an academic session is active.
// Once a new session is activated the old one stops working.
$activeSession = pt_active_session();
if (!$activeSession) {
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Bookings are currently closed. No active session.'));
    exit;
}
$sessionId = (int)$activeSession['id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Always use the session user id (prevents IDOR)
    $userId = (int)$_SESSION['user_id'];
    $bankName = isset($_POST['bankName']) ? trim($_POST['bankName']) : '';
    $payersName = isset($_POST['payers_name']) ? trim($_POST['payers_name']) : '';

    if ($bankName === '' || $payersName === '') {
        $response['error'] = "Bank name and payer name are required";
    } else {
        // Check if user has already submitted payment info for this session
        $stmt_check = $conn->prepare("SELECT userId FROM payments WHERE userId = ? AND session_id = ?");
        $stmt_check->bind_param("ii", $userId, $sessionId);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // User has already submitted payment info
            $response['error'] = "User has already submitted payment information";
        } else {
            // Handle file upload if a file is selected
            if (isset($_FILES['paymentInfo']) && $_FILES['paymentInfo']['error'] === UPLOAD_ERR_OK) {
                // Enforce a size limit (5MB)
                if ((int)$_FILES['paymentInfo']['size'] <= 0 || (int)$_FILES['paymentInfo']['size'] > 5242880) {
                    $response['error'] = "File is too large. Maximum allowed size is 5MB.";
                } else {
                    // Detect the real file type instead of trusting the client MIME header
                    $allowed_types = array('application/pdf', 'image/jpeg', 'image/jpg', 'image/png');
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $file_type = $finfo ? finfo_file($finfo, $_FILES['paymentInfo']['tmp_name']) : $_FILES['paymentInfo']['type'];
                    if ($finfo) {
                        finfo_close($finfo);
                    }

                    if (!in_array($file_type, $allowed_types)) {
                        $response['error'] = "File type not supported. Please upload PDF, JPEG, or PNG files.";
                    } else {
                        // Get file data
                        $fileTmpPath = $_FILES['paymentInfo']['tmp_name'];
                        $fileData = file_get_contents($fileTmpPath);

                        // Prepare SQL query
                        $stmt_insert = $conn->prepare("INSERT INTO payments (userId, session_id, paymentInfo, bankName, payers_name, status, uploadDate) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
                        $stmt_insert->bind_param("iisss", $userId, $sessionId, $fileData, $bankName, $payersName);

                        // Execute the query
                        if ($stmt_insert->execute()) {
                            // Success
                            $response['success'] = "Payment information uploaded successfully";
                        } else {
                            // Error
                            $response['error'] = "Failed to upload payment information";
                        }

                        $stmt_insert->close();
                    }
                }
            } else {
                // No file selected
                $response['error'] = "Please select a file to upload";
            }
        }

        $stmt_check->close();
    }
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
