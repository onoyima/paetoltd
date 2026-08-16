<?php
session_start();
$_SESSION['user_id'] = 31;
$_SESSION['email'] = 'katrynv7@gmail.com';
$_SESSION['username'] = 'katrynv7';
$_SESSION['role'] = 'student';
$_SESSION['timeout'] = time() + 1800;
header('Location: book-hostel.php');
exit;
