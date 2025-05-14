<?php
// Set content type to plain text for debugging
header('Content-Type: text/plain');

// Include database connection
require_once 'db_connect.php';

// Check if there was a database connection error
if (isset($db_connection_error) && !empty($db_connection_error)) {
    echo "Database connection error: " . $db_connection_error;
    exit;
}

// Check if the table exists
$tableCheckQuery = "SHOW TABLES LIKE 'government_representatives'";
$result = $conn->query($tableCheckQuery);

if ($result && $result->num_rows > 0) {
    echo "Table 'government_representatives' exists.\n\n";
    
    // Check table structure
    $describeQuery = "DESCRIBE government_representatives";
    $describeResult = $conn->query($describeQuery);
    
    if ($describeResult) {
        echo "Table structure:\n";
        while ($row = $describeResult->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Error checking table structure: " . $conn->error;
    }
} else {
    echo "Table 'government_representatives' does not exist. Creating it now...\n";
    
    // Create the table
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `government_representatives` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `employee_id` varchar(50) NOT NULL,
      `email` varchar(255) NOT NULL,
      `department` varchar(100) NOT NULL,
      `position` varchar(100) NOT NULL,
      `region` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      `last_login` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      UNIQUE KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($createTableSQL)) {
        echo "Table created successfully.";
    } else {
        echo "Error creating table: " . $conn->error;
    }
}

// Close the connection
$conn->close();


?>
