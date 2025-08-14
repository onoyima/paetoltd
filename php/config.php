<?php
// session_start();
ob_start();
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'paetos';
$username = 'root';
$password = 'root';
$port = 8889; // MAMP default port for MySQL

// Path to the MySQL socket file for MAMP
$unix_socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

// Attempt to establish a MySQLi connection
if ($host == 'localhost' && file_exists($unix_socket)) {
    // Use Unix socket if host is localhost and socket file exists
    $conn = new mysqli($host, $username, $password, $dbname, null, $unix_socket);
} else {
    // Use TCP/IP connection
    $conn = new mysqli($host, $username, $password, $dbname, $port);
}

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// CONSTANT VARIABLES
// define('XDATE', date('d/m/Y'));
?>
