<?php
session_start();
include 'config.php'; // adjust path

// Ensure user is logged in and session is active
if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
    echo "Error: Not logged in";
    exit;
}

$_SESSION['timeout'] = time() + 1800;

$user_id = (int)$_SESSION['user_id'];

if (isset($_FILES['userImage']) && $_FILES['userImage']['error'] === 0) {
    // Enforce a reasonable size limit (2MB)
    if ((int)$_FILES['userImage']['size'] <= 0 || (int)$_FILES['userImage']['size'] > 2097152) {
        echo "Error: Image must be smaller than 2MB.";
        exit;
    }

    // Validate the real image type (don't trust the client-provided MIME type)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realType = $finfo ? finfo_file($finfo, $_FILES['userImage']['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png');
    if (!in_array($realType, $allowed_types, true)) {
        echo "Error: Only JPEG, JPG or PNG images are allowed.";
        exit;
    }

    $imageData = file_get_contents($_FILES['userImage']['tmp_name']);

    $null = null;
    $stmt = $conn->prepare("UPDATE userregistration SET userImage = ? WHERE id = ?");
    $stmt->bind_param("bi", $null, $user_id);

    // send as blob
    $stmt->send_long_data(0, $imageData);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
} else {
    echo "No image uploaded.";
}

$conn->close();
?>
