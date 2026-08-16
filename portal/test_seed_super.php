<?php
session_start();
$_SESSION['user_id'] = 8;
$_SESSION['email'] = 'clintonfaze@gmail.com';
$_SESSION['username'] = 'clintonfaze';
$_SESSION['role'] = 'superadmin';
$_SESSION['timeout'] = time() + 1800;
header('Location: confirm-payments.php');
exit;

