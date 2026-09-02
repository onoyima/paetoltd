<?php
include 'config.php';
$res = $conn->query("SHOW CREATE TABLE payments");
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
$conn->close();
?>
