<?php
include 'php/config.php';
require 'php/fetch_payment.php';
$paymentsAll = fetchUserPayments($conn, 2, 0);
$paymentsH1 = fetchUserPayments($conn, 2, 1);
$paymentsH2 = fetchUserPayments($conn, 2, 2);
echo "All: " . count($paymentsAll) . "\n";
echo "H1: " . count($paymentsH1) . "\n";
echo "H2: " . count($paymentsH2) . "\n";
?>
