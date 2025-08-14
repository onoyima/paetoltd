<?php
// Assuming you have a database connection established
include 'config.php';


if (isset($_POST['userid'])) {
    $userid = $_POST['userid'];

    // Prepare SQL query
    $sql = "SELECT u.firstName, u.lastName, u.userImage, p.paymentInfo, p.status
            FROM payments p
            INNER JOIN userregistration u ON p.userId = u.id
            WHERE p.userId = :userid";

    // Execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Return JSON response
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No data found']);
    }
} else {
    echo json_encode(['error' => 'User ID not provided']);
}
?>
