<?php
include 'config.php';

function fetchUserPayments($conn, $limit = 1000, $offset = 0) {
    $sql = "SELECT u.id, u.regNo, u.firstName, u.middleName, u.lastName, u.gender, u.contactNo, u.email, p.status, p.room, p.bed, p.bankName, p.payers_name, p.uploadDate 
            FROM userregistration u 
            LEFT JOIN payments p ON u.id = p.userId 
            WHERE p.id IS NOT NULL
            ORDER BY p.uploadDate ASC 
            LIMIT ? OFFSET ?"; 
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $rows = array();
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    } else {
        $stmt->close();
        return array();
    }
}

function getTotalPaymentCount($conn) {
    $sql = "SELECT COUNT(*) as total FROM userregistration u LEFT JOIN payments p ON u.id = p.userId WHERE p.id IS NOT NULL";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$userPayments = fetchUserPayments($conn);
$conn->close();
?>
