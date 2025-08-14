<?php
session_start();
include 'php/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

// Fetch unconfirmed payments
$stmt = $conn->prepare("SELECT * FROM payments WHERE status = 'Pending'");
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Confirm Payments</title>
</head>
<body>
    <h1>Unconfirmed Payments</h1>
    <table border="1">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Payment Info</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['userId']; ?></td>
                <td><a href="view_payment.php?id=<?php echo $row['id']; ?>" target="_blank">View</a></td>
                <td>
                    <form action="confirm_payment.php" method="post">
                        <input type="hidden" name="paymentId" value="<?php echo $row['id']; ?>">
                        <label for="room">Room Number:</label>
                        <input type="text" name="room" required>
                        <label for="bed">Bed Number:</label>
                        <input type="text" name="bed" required>
                        <button type="submit">Confirm</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
