<?php

// Database connection parameters
define('DB_SERVER', 'sql200.infinityfree.com');     
define('DB_USERNAME', 'if0_39013957');         
define('DB_PASSWORD', '');           
define('DB_NAME', 'if0_39013957_government_portal');  

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// --- Check Connection ---
if ($conn->connect_error) {
    $db_connection_error = 'Database connection failed: ' . $conn->connect_error;
    // Don't die or output anything here
}

// Character Set 
if (!$conn->connect_error && !$conn->set_charset("utf8mb4")) {
    $db_connection_error = 'Error loading character set utf8mb4: ' . $conn->error;
}
?>
