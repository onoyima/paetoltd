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
$password = 'root';

// Path to the MySQL socket file for MAMP (macOS)
$unix_socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

$conn = null;

// Use return-value style (no exceptions) so failed attempts can fall through to the next port
mysqli_report(MYSQLI_REPORT_OFF);

// Cap connection attempts so a dead/firewalled port fails fast instead of hanging ~4s
ini_set('mysqli.connect_timeout', '2');
ini_set('default_socket_timeout', '2');

// Attempt to establish a MySQLi connection
if ($host == 'localhost' && file_exists($unix_socket)) {
    // Use Unix socket if host is localhost and socket file exists (MAMP on macOS)
    $conn = @new mysqli($host, $username, $password, $dbname, null, $unix_socket);
}

if (!$conn || $conn->connect_error) {
    // Fall back to TCP/IP. Try the XAMPP default (3306) first, then MAMP (8889),
    // so the common XAMPP case connects immediately and the dead 8889 port is
    // only probed when 3306 is unavailable.
    foreach (array(3306, 8889) as $port) {
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

// PHPMailer SMTP configuration
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USERNAME', 'c37ef4508c01e6');
define('SMTP_PASSWORD', '25db67cf9f349e');
define('SMTP_FROM_EMAIL', 'noreply@paetosltd.ng');
define('SMTP_FROM_NAME', 'Pa-etos Portal');
define('PASSWORD_RESET_BASE_URL', 'https://paetosltd.ng/portal');
define('PASSWORD_RESET_EXPIRY_MINUTES', 30);
?>
