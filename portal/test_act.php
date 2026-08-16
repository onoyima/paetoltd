<?php
require_once 'C:/xampp/htdocs/paetos/portal/php/config.php';
require_once 'C:/xampp/htdocs/paetos/portal/php/rbac.php';
session_start();
$_SESSION['user_id'] = 6;
$_SESSION['role'] = 'superadmin';
session_write_close();

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'activate';
$_POST['id'] = '2';

include 'C:/xampp/htdocs/paetos/portal/php/session_api.php';