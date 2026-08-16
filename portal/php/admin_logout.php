<?php
session_start(); // Resume session

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear remember me cookie if set
if (isset($_COOKIE['remember_user'])) {
    unset($_COOKIE['remember_user']);
    setcookie('remember_user', null, -1, '/');
}

// Redirect to login page
header("Location: ../admin_login.html");
exit();
?>
