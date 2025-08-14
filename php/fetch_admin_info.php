<?php
// Include config file to connect to the database
include 'config.php';

// Initialize variables to store admin information
$admin = array(); // Initialize an empty array to store admin data

// Check if user is logged in and has admin role (this should ideally be checked before including this file)
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Retrieve admin ID from session
    $admin_id = $_SESSION['user_id']; // Assuming user_id is the admin's unique identifier in the database

    // Query to fetch admin information based on user ID
    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch admin data
        $admin = $result->fetch_assoc();
    } else {
        // Handle case where admin data is not found (though this should not occur if user is authenticated as admin)
        $admin = array(); // Set admin data to empty array
    }

    $stmt->close();
}

$conn->close();

// Return $admin array to be used in admin_dashboard.php




?>
