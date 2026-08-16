<?php
include 'config.php';
require_once __DIR__ . '/rbac.php';

$admin = array();

if (pt_is_admin()) {
    $admin_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, username, email, role FROM admin WHERE id = ?");
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    }

    $stmt->close();
}
// $conn is intentionally left open so including pages may reuse it.
?>
