<?php
include 'config.php';

function fetchUserPayments($conn) {
    $sql = "SELECT u.id, u.regNo, u.firstName, u.middleName, u.lastName, u.gender, u.contactNo, u.email, u.userImage, p.paymentInfo, p.status, p.room, p.bed, p.bankName, p.payers_name, p.uploadDate 
            FROM userregistration u 
            LEFT JOIN payments p ON u.id = p.userId";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $rows = array();
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    } else {
        return array(); 
    }
}

$userPayments = fetchUserPayments($conn);
$conn->close();
?>
