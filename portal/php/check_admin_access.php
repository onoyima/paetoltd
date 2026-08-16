<?php
require_once __DIR__ . '/rbac.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$permission = isset($_GET['perm']) ? $_GET['perm'] : 'dashboard';

header('Content-Type: application/json');
echo json_encode(array('can' => pt_can($permission)));
?>
