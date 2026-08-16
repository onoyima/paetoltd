<?php
require 'db_connection.php'; // Make sure to create a db_connection.php file to handle the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_name = $_POST['room_name'];
    $total_beds = $_POST['total_beds'];

    addRoom($room_name, $total_beds, $conn);
    $conn->close();
}
?> <?php
require 'db_connection.php'; // Make sure to create a db_connection.php file to handle the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_name = $_POST['room_name'];
    $total_beds = $_POST['total_beds'];

    addRoom($room_name, $total_beds, $conn);
    $conn->close();
}
?> <?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST['admin_id'];
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];

    assignBedSpace($admin_id, $user_id, $room_id, $conn);
    $conn->close();
}
?>
<?php
require 'db_connection.php';

$rooms = viewRoomsAndBeds($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rooms and Beds</title>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Rooms and Beds</h1>
        <?php
        foreach ($rooms as $room_id => $room) {
            echo "<h3>Room: " . $room['room_name'] . " (Total Beds: " . $room['total_beds'] . ", Available Beds: " . $room['available_beds'] . ")</h3>";
            foreach ($room['beds'] as $bed) {
                echo "<p>Bed ID: " . $bed['bed_id'] . " - Assigned to User ID: " . ($bed['assigned'] ? $bed['user_id'] : "Not Assigned") . "</p>";
            }
        }
        ?>
    </div>
</body>
</html> <?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];

    removeRoom($room_id, $conn);
    $conn->close();
}
?> <?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST['admin_id'];
    $new_user_id = $_POST['new_user_id'];
    $bed_id = $_POST['bed_id'];

    reassignBedSpace($admin_id, $new_user_id, $bed_id, $conn);
    $conn->close();
}
?>