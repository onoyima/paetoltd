<?php
// Simple script to test if fetch_assigned_room.php returns valid JSON

// Execute the fetch_assigned_room.php script and capture its output
$output = file_get_contents('http://localhost:8888/php/fetch_assigned_room.php');

// Try to decode the JSON
$decoded = json_decode($output, true);

// Check if the JSON is valid
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Valid JSON returned:\n";
    print_r($decoded);
} else {
    echo "Invalid JSON returned. Error: " . json_last_error_msg() . "\n";
    echo "Raw output:\n";
    echo $output;
}
?>