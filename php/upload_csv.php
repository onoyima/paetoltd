<?php
header('Content-Type: application/json');
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php'; // Database connection

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    // Check if file was uploaded without errors
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
        throw new Exception('Error uploading file. Please try again.');
    }

    $file = $_FILES['csv_file'];
    
    // Check file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext !== 'csv') {
        throw new Exception('Only CSV files are allowed.');
    }
    
    // Open uploaded CSV file with read-only mode
    $csvFile = fopen($file['tmp_name'], 'r');
    
    // Skip the first line (header)
    fgetcsv($csvFile);
    
    // Counter for successful imports
    $importCount = 0;
    $errorCount = 0;
    $errors = [];
    
    // Read data line by line
    while (($row = fgetcsv($csvFile)) !== FALSE) {
        // Check if we have enough columns
        if (count($row) < 7) {
            $errorCount++;
            $errors[] = "Row skipped: Not enough columns";
            continue;
        }
        
        // Extract data from CSV
        $student_name = $row[0];
        $matric_no = $row[1];
        $department = $row[2];
        $parent_number = $row[3];
        $level = $row[4];
        $student_number = $row[5];
        $room_bunk = $row[6];
        
        // Check if room_bunk already exists
        $checkStmt = $conn->prepare("SELECT sn FROM assign_room WHERE room_bunk = ?");
        $checkStmt->bind_param("s", $room_bunk);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // Room bunk exists, update student information directly using room_bunk as the key
            $updateStmt = $conn->prepare("UPDATE assign_room SET 
                                student_name = ?, 
                                matric_no = ?, 
                                department = ?, 
                                parent_number = ?, 
                                level = ?, 
                                student_number = ? 
                                WHERE room_bunk = ?");
            $updateStmt->bind_param("sssssss", $student_name, $matric_no, $department, $parent_number, $level, $student_number, $room_bunk);
            
            if ($updateStmt->execute()) {
                $importCount++;
            } else {
                $errorCount++;
                $errors[] = "Error updating record for room bunk: $room_bunk. Error: " . $updateStmt->error;
            }
            
            $updateStmt->close();
        } else {
            // Room bunk doesn't exist, insert new record
            $insertStmt = $conn->prepare("INSERT INTO assign_room 
                                (student_name, matric_no, department, parent_number, level, student_number, room_bunk) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssssss", $student_name, $matric_no, $department, $parent_number, $level, $student_number, $room_bunk);
            
            if ($insertStmt->execute()) {
                $importCount++;
            } else {
                $errorCount++;
                $errors[] = "Error inserting record for room bunk: $room_bunk. Error: " . $insertStmt->error;
            }
            
            $insertStmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Close opened CSV file
    fclose($csvFile);
    
    if ($importCount > 0) {
        $response['status'] = 'success';
        $response['message'] = "$importCount records imported successfully.";
        if ($errorCount > 0) {
            $response['message'] .= " $errorCount records failed.";
            $response['errors'] = $errors;
        }
    } else {
        throw new Exception('No records were imported. Please check your CSV file.');
    }
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>