<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once 'config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    if (!isset($_POST['search_query']) || empty(trim($_POST['search_query']))) {
        throw new Exception('Search query is required');
    }

    $searchQuery = trim($_POST['search_query']);
    
    // Prepare SQL query to search in assign_room table
    // Search by matric_no, student_number (phone), or parent_number
    $sql = "SELECT 
                ar.id,
                ar.student_name,
                ar.matric_no,
                ar.department,
                ar.level,
                ar.student_number,
                ar.parent_number,
                ar.room_bunk,
                ar.created_at,
                ar.updated_at
            FROM assign_room ar 
            WHERE (ar.matric_no = ? OR ar.student_number = ? OR ar.parent_number = ?) 
            AND ar.matric_no IS NOT NULL 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('sss', $searchQuery, $searchQuery, $searchQuery);
    
    if (!$stmt->execute()) {
        throw new Exception('Database execution failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Return success response with student data
        echo json_encode([
            'success' => true,
            'message' => 'Student found',
            'student' => $student
        ]);
    } else {
        // No student found
        echo json_encode([
            'success' => false,
            'message' => 'No student found with the provided details',
            'student' => null
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'student' => null
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>