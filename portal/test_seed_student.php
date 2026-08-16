<?php
session_start();
$_SESSION['user_id'] = 31;
$_SESSION['timeout'] = time() + 1800;
header('Location: book-hostel.php');
exit;

