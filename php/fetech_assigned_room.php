<?php
header('Content-Type: application/json');
include 'config.php'; // Database connection

$response = ['status' => 'error', 'message' => 'Unknown error', 'users' => []];

try {
    // Query to fetch assigned rooms and student details
    $sql = "SELECT 
                regNo, 
                firstName, 
               middleName, 
               lastName, 
               gender, 
               contactNo, 
               email, 
                room_number, 
                bed_space,
                room_type,
                created_at
            FROM assign_room 
           
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'regNo' => $row['regNo'],
            'firstName' => $row['firstName'],
            'middleName' => $row['middleName'],
            'lastName' => $row['lastName'],
            'gender' => $row['gender'],
            'contactNo' => $row['contactNo'],
            'email' => $row['email'],
            'room_number' => $row['room_number'],
            'bed_space' => $row['bed_space'],
            'room_type' => $row['room_type'],
            'created_at' => $row['created_at']
        ];
    }

    $response['status'] = 'success';
    $response['message'] = 'Data fetched successfully';
    $response['users'] = $users;

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
