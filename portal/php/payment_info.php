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
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Bookings are currently closed. No active session.'));
    exit;
}
$sessionId = (int)$activeSession['id'];

/* ------------------------------------------------------------------
   Image helpers: receipts are saved to disk (uploads/payments) and
   compressed to at most 2MB so the system stays fast. The old BLOB
   column is left untouched for legacy rows.
   ------------------------------------------------------------------ */
function pt_save_payment_image($srcTmp, $dstPath, $maxBytes = 2097152, &$errorMsg = null) {
    if (!function_exists('imagecreatetruecolor')) {
        if (filesize($srcTmp) <= $maxBytes) {
            if (copy($srcTmp, $dstPath)) {
                return true;
            } else {
                $errorMsg = "GD missing, and failed to copy file.";
                return false;
            }
        }
        $errorMsg = "GD library is not installed for compression, and image is over 2MB.";
        return false;
    }

    $info = @getimagesize($srcTmp);
    if (!$info) {
        $errorMsg = "Not a valid image (getimagesize failed).";
        return false;
    }

    $mime = $info['mime'];
    $img = null;
    switch ($mime) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($srcTmp); break;
        case 'image/png':  $img = @imagecreatefrompng($srcTmp); break;
        case 'image/gif':  $img = @imagecreatefromgif($srcTmp); break;
        case 'image/webp': $img = @imagecreatefromwebp($srcTmp); break;
        default:           
            $errorMsg = "Unsupported image format: " . $mime;
            return false;
    }
    if (!$img) {
        $errorMsg = "Could not decode image (possibly corrupted).";
        return false;
    }

    $w = imagesx($img);
    $h = imagesy($img);

    // Cap dimensions so huge camera photos don't inflate the file size
    $maxDim = 2000;
    if (max($w, $h) > $maxDim) {
        $scale = $maxDim / max($w, $h);
        $nw = max(1, (int)round($w * $scale));
        $nh = max(1, (int)round($h * $scale));
        $resized = imagecreatetruecolor($nw, $nh);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $nw, $nh, $white);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $resized;
        $w = $nw;
        $h = $nh;
    }

    $data = null;
    $quality = 85;
    while ($quality >= 30) {
        ob_start();
        imagejpeg($img, null, $quality);
        $data = ob_get_clean();
        if (strlen($data) <= $maxBytes) {
            break;
        }
        $quality -= 10;
    }

    // Still too big? Shrink the image in half-steps until it fits
    $guard = 0;
    while ($data !== null && strlen($data) > $maxBytes && max($w, $h) > 200 && $guard < 12) {
        $nw = max(1, (int)round($w / 2));
        $nh = max(1, (int)round($h / 2));
        $resized = imagecreatetruecolor($nw, $nh);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $nw, $nh, $white);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $resized;
        $w = $nw;
        $h = $nh;
        ob_start();
        imagejpeg($img, null, 70);
        $data = ob_get_clean();
        $guard++;
    }
    imagedestroy($img);

    if ($data === null || strlen($data) > $maxBytes) {
        $errorMsg = "Image could not be compressed under 2MB.";
        return false;
    }

    if (file_put_contents($dstPath, $data) === false) {
        $errorMsg = "Failed to write image to disk.";
        return false;
    }
    return true;
}

// Return JSON response — clear any buffered output first
function send_json_response($response) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Always use the session user id (prevents IDOR)
        $userId = (int)$_SESSION['user_id'];
        $bankName = isset($_POST['bankName']) ? trim($_POST['bankName']) : '';
        $payersName = isset($_POST['payers_name']) ? trim($_POST['payers_name']) : '';
        $hostelId = isset($_POST['hostel_id']) ? (int)$_POST['hostel_id'] : 0;

        if ($bankName === '' || $payersName === '') {
            $response['error'] = "Bank name and payer name are required";
        } elseif ($hostelId <= 0 || !in_array($hostelId, array_map('intval', array_column(pt_all_hostels(), 'id')), true)) {
            $response['error'] = "Please select a valid hostel";
        } elseif (!isset($_FILES['paymentInfo']) || $_FILES['paymentInfo']['error'] !== UPLOAD_ERR_OK) {
            $response['error'] = "Please select a file to upload";
        } else {
            $file = $_FILES['paymentInfo'];

            // Detect the real file type if Fileinfo extension is installed, else fallback to client mime
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileType = $finfo ? finfo_file($finfo, $file['tmp_name']) : $file['type'];
                if ($finfo) finfo_close($finfo);
            } else {
                $fileType = $file['type'];
            }

            $allowedTypes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
            if (!in_array($fileType, $allowedTypes, true)) {
                $response['error'] = "Only image files (JPEG, PNG, GIF, WebP) are allowed.";
            } else {
                $uploadDir = __DIR__ . '/../uploads/payments/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
                    $response['error'] = "Upload folder is not writable.";
                } else {
                    $fileName = 'pay_' . $userId . '_' . $sessionId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
                    $relPath = 'uploads/payments/' . $fileName;
                    $absPath = $uploadDir . $fileName;

                    $errorMsg = "";
                    if (!pt_save_payment_image($file['tmp_name'], $absPath, 2097152, $errorMsg)) {
                        $response['error'] = "Could not process the image. " . $errorMsg . " Please upload a valid image under 2MB.";
                    } else {
                        // Check if already submitted
                        $stmt_check = $conn->prepare("SELECT userId FROM payments WHERE userId = ? AND session_id = ?");
                        if (!$stmt_check) throw new Exception("Prepare check failed: " . $conn->error);
                        $stmt_check->bind_param("ii", $userId, $sessionId);
                        $stmt_check->execute();
                        $stmt_check->store_result();

                        if ($stmt_check->num_rows > 0) {
                            $stmt_check->close();
                            @unlink($absPath);
                            $response['error'] = "User has already submitted payment information";
                        } else {
                            $stmt_check->close();

                            $stmt_insert = $conn->prepare("INSERT INTO payments (userId, session_id, hostel_id, paymentInfo, payment_file, bankName, payers_name, status, uploadDate) VALUES (?, ?, ?, NULL, ?, ?, ?, 'Pending', NOW())");
                            if (!$stmt_insert) throw new Exception("Prepare insert failed: " . $conn->error);
                            
                            $stmt_insert->bind_param("iissss", $userId, $sessionId, $hostelId, $relPath, $bankName, $payersName);

                            if ($stmt_insert->execute()) {
                                $response['success'] = "Payment information uploaded successfully";
                            } else {
                                @unlink($absPath);
                                $response['error'] = "Failed to upload payment information: " . $stmt_insert->error;
                            }
                            $stmt_insert->close();
                        }
                    }
                }
            }
        }
    }
} catch (Throwable $e) {
    $response['error'] = "System Error: " . $e->getMessage();
}

if (isset($conn) && $conn) $conn->close();
send_json_response($response);
