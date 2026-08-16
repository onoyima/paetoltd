<?php
// Public search endpoint (used by the "Check Your Room" feature on the landing pages)
header('Content-Type: application/json');

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
    
    // Prepare phone number variations for search
    $phoneVariations = [];
    
    // If the search query looks like a phone number (all digits)
    if (ctype_digit($searchQuery)) {
        // Original number
        $phoneVariations[] = $searchQuery;
        
        // If it starts with 0, also try without the leading 0
        if (strlen($searchQuery) > 1 && $searchQuery[0] === '0') {
            $phoneVariations[] = substr($searchQuery, 1);
        }
        // If it doesn't start with 0, also try with a leading 0
        else {
            $phoneVariations[] = '0' . $searchQuery;
        }
    } else {
        // Not a phone number, use as is (for matric_no)
        $phoneVariations[] = $searchQuery;
    }

    // Build the SQL query with multiple phone number variations
    $conditions = [];
    $params = [];
    $types = '';

    // Always search by matric_no (exact match)
    $conditions[] = "ar.matric_no = ?";
    $params[] = $searchQuery;
    $types .= 's';

    // Add phone number variations for student_number and parent_number
    foreach ($phoneVariations as $variation) {
        $conditions[] = "ar.student_number = ?";
        $conditions[] = "ar.parent_number = ?";
        $params[] = $variation;
        $params[] = $variation;
        $types .= 'ss';
    }

    $whereClause = "(" . implode(" OR ", $conditions) . ")";

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
    WHERE $whereClause
    AND ar.matric_no IS NOT NULL
    LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

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
    // Return error response (without leaking internal error details)
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching. Please try again.',
        'student' => null
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>