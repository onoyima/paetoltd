<?php
function addRoom($room_name, $total_beds, $conn) {
    // Insert room details
    $query = "INSERT INTO rooms (room_name, total_beds, available_beds) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $room_name, $total_beds, $total_beds);
    $stmt->execute();
    $room_id = $stmt->insert_id;
    $stmt->close();
    
    // Insert bed details
    $query = "INSERT INTO beds (room_id) VALUES ";
    for ($i = 0; $i < $total_beds; $i++) {
        $query .= "($room_id),";
    }
    $query = rtrim($query, ",");
    $conn->query($query);
    
    echo "Room and bed spaces added successfully.";
}

// Database connection
$conn = new mysqli("localhost", "username", "password", "paetos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example usage
addRoom("Room 101", 4, $conn);

$conn->close();
?>  <?php
function assignBedSpace($admin_id, $user_id, $room_id, $conn) {
    // Check if the user is already assigned a bed
    $query = "SELECT bed_id FROM beds WHERE user_id = ? AND assigned = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo "User is already assigned a bed.";
        return;
    }
    $stmt->close();
    
    // Check for available beds
    $query = "SELECT bed_id FROM beds WHERE room_id = ? AND assigned = FALSE LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($bed_id);
        $stmt->fetch();
        $stmt->close();
        
        // Assign bed space to user
        $query = "UPDATE beds SET user_id = ?, assigned = TRUE WHERE bed_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $bed_id);
        $stmt->execute();
        $stmt->close();
        
        // Update available beds count in room
        $query = "UPDATE rooms SET available_beds = available_beds - 1 WHERE room_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $stmt->close();
        
        // Log the assignment
        $query = "INSERT INTO assignment_log (admin_id, user_id, room_id, bed_id, assigned_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $admin_id, $user_id, $room_id, $bed_id);
        $stmt->execute();
        $stmt->close();
        
        echo "Bed space assigned successfully.";
    } else {
        $stmt->close();
        echo "No available beds in the specified room.";
    }
}

// Database connection
$conn = new mysqli("localhost", "username", "password", "paetos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example usage
assignBedSpace(1, 2, 1, $conn);

$conn->close();
?>
<?php
function viewRoomsAndBeds($conn) {
    $query = "SELECT r.room_id, r.room_name, r.total_beds, r.available_beds, 
                     b.bed_id, b.user_id, b.assigned 
              FROM rooms r 
              LEFT JOIN beds b ON r.room_id = b.room_id 
              ORDER BY r.room_id, b.bed_id";
    $result = $conn->query($query);
    
    $rooms = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $room_id = $row['room_id'];
            if (!isset($rooms[$room_id])) {
                $rooms[$room_id] = [
                    'room_name' => $row['room_name'],
                    'total_beds' => $row['total_beds'],
                    'available_beds' => $row['available_beds'],
                    'beds' => []
                ];
            }
            $rooms[$room_id]['beds'][] = [
                'bed_id' => $row['bed_id'],
                'user_id' => $row['user_id'],
                'assigned' => $row['assigned']
            ];
        }
    }

    return $rooms;
}

// Database connection
$conn = new mysqli("localhost", "username", "password", "paetos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example usage
$rooms = viewRoomsAndBeds($conn);
foreach ($rooms as $room_id => $room) {
    echo "Room: " . $room['room_name'] . " (Total Beds: " . $room['total_beds'] . ", Available Beds: " . $room['available_beds'] . ")\n";
    foreach ($room['beds'] as $bed) {
        echo "  Bed ID: " . $bed['bed_id'] . " - Assigned to User ID: " . ($bed['assigned'] ? $bed['user_id'] : "Not Assigned") . "\n";
    }
}

$conn->close();
?>
<?php
function removeRoom($room_id, $conn) {
    // Delete associated beds
    $query = "DELETE FROM beds WHERE room_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete the room
    $query = "DELETE FROM rooms WHERE room_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();
    
    echo "Room and its bed spaces removed successfully.";
}

// Database connection
$conn = new mysqli("localhost", "username", "password", "paetos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example usage
removeRoom(1, $conn);

$conn->close();
?>

<?php
function reassignBedSpace($admin_id, $new_user_id, $bed_id, $conn) {
    // Get current user assigned to the bed
    $query = "SELECT user_id FROM beds WHERE bed_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $bed_id);
    $stmt->execute();
    $stmt->bind_result($current_user_id);
    $stmt->fetch();
    $stmt->close();
    
    // Ensure the bed is currently assigned
    if ($current_user_id) {
        // Update the bed assignment
        $query = "UPDATE beds SET user_id = ?, assigned = TRUE WHERE bed_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $new_user_id, $bed_id);
        $stmt->execute();
        $stmt->close();
        
        // Log the reassignment
        $query = "INSERT INTO assignment_log (admin_id, user_id, room_id, bed_id, assigned_at) 
                  VALUES (?, ?, (SELECT room_id FROM beds WHERE bed_id = ?), ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $admin_id, $new_user_id, $bed_id, $bed_id);
        $stmt->execute();
        $stmt->close();
        
        echo "Bed space reassigned successfully.";
    } else {
        echo "The specified bed space is not currently assigned.";
    }
}

// Database connection
$conn = new mysqli("localhost", "username", "password", "paetos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example usage
reassignBedSpace(1, 3, 2, $conn);

$conn->close();
?>
  