<?php
$t0 = microtime(true);

// 1) session_start
session_start();
$t1 = microtime(true);

// 2) direct 3306
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli('localhost', 'doncassa_pat', '!JD-E17mJ%;9b!^{', 'doncassa_pat', 3306);
$t2 = microtime(true);

// 3) trivial query
$r = $conn->query("SELECT COUNT(*) c FROM userregistration");
$t3 = microtime(true);
$row = $r->fetch_assoc();

// 4) the config.php fallback path (8889 first)
$conn2 = null;
foreach (array(8889, 3306) as $port) {
    $conn2 = @new mysqli('localhost', 'doncassa_pat', '!JD-E17mJ%;9b!^{', 'doncassa_pat', $port);
    if (!$conn2->connect_error) { break; }
}
$t4 = microtime(true);

header('Content-Type: application/json');
echo json_encode([
    'session_start_ms' => round(($t1-$t0)*1000),
    'direct_3306_ms'   => round(($t2-$t1)*1000),
    'trivial_query_ms' => round(($t3-$t2)*1000),
    'count'            => $row['c'],
    'fallback_8889_3306_ms' => round(($t4-$t3)*1000),
    'conn2_ok'         => !$conn2->connect_error,
]);
