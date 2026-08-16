<?php
// Guard against double-include (auth_admin.php may include config before endpoints do)
if (isset($GLOBALS['pt_config_loaded'])) {
    return;
}
$GLOBALS['pt_config_loaded'] = true;

// session_start();
ob_start();
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'doncassa_pat';
$username = 'doncassa_pat';
$password = '!JD-E17mJ%;9b!^{';

// Path to the MySQL socket file for MAMP (macOS)
$unix_socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

$conn = null;

// Use return-value style (no exceptions) so failed attempts can fall through to the next port
mysqli_report(MYSQLI_REPORT_OFF);

// Attempt to establish a MySQLi connection
if ($host == 'localhost' && file_exists($unix_socket)) {
    // Use Unix socket if host is localhost and socket file exists (MAMP on macOS)
    $conn = @new mysqli($host, $username, $password, $dbname, null, $unix_socket);
}

if (!$conn || $conn->connect_error) {
    // Fall back to TCP/IP, trying MAMP default port (8889) then XAMPP default (3306)
    foreach (array(8889, 3306) as $port) {
        $conn = @new mysqli($host, $username, $password, $dbname, $port);
        if (!$conn->connect_error) {
            break;
        }
    }
}

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Use utf8mb4 so student names / payment details are stored and compared safely
$conn->set_charset('utf8mb4');
?>
