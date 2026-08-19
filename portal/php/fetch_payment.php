<?php
include 'config.php';
require_once __DIR__ . '/academic_helper.php';

function fetchUserPayments($conn, $sessionId = 0, $hostelId = 0, $limit = 1000, $offset = 0) {
    $where = array('p.id IS NOT NULL');
    $params = array();
    $types = '';

    if ($sessionId > 0) {
        $where[] = 'p.session_id = ?';
        $params[] = $sessionId;
        $types .= 'i';
    }
    if ($hostelId > 0) {
        $where[] = 'p.hostel_id = ?';
        $params[] = $hostelId;
        $types .= 'i';
    }

    $sql = "SELECT u.id, u.regNo, u.firstName, u.middleName, u.lastName, u.gender, u.contactNo, u.email,
                   p.id AS payment_id, p.status, p.room, p.bed, p.bankName, p.payers_name, p.uploadDate,
                   p.session_id, s.name AS session_name, p.hostel_id, h.name AS hostel_name
            FROM userregistration u
            INNER JOIN payments p ON u.id = p.userId
            LEFT JOIN academic_session s ON p.session_id = s.id
            LEFT JOIN hostel h ON p.hostel_id = h.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.uploadDate ASC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    } else {
        $stmt->close();
        return array();
    }
}

function getTotalPaymentCount($conn, $sessionId = 0, $hostelId = 0) {
    $where = array('p.id IS NOT NULL');
    $params = array();
    $types = '';

    if ($sessionId > 0) {
        $where[] = 'p.session_id = ?';
        $params[] = $sessionId;
        $types .= 'i';
    }
    if ($hostelId > 0) {
        $where[] = 'p.hostel_id = ?';
        $params[] = $hostelId;
        $types .= 'i';
    }

    $sql = "SELECT COUNT(*) AS total
            FROM userregistration u
            INNER JOIN payments p ON u.id = p.userId
            WHERE " . implode(' AND ', $where);

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row['total'];
}

$sessionFilter = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$hostelFilter = isset($_GET['hostel_id']) ? (int)$_GET['hostel_id'] : 0;

// Default to active session if none specified
if ($sessionFilter === 0) {
    $active = pt_active_session();
    if ($active) {
        $sessionFilter = (int)$active['id'];
    }
}

$userPayments = fetchUserPayments($conn, $sessionFilter, $hostelFilter);
$conn->close();
?>
