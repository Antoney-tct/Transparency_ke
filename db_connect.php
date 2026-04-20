<?php
$servername = "sql100.infinityfree.com";
$username = "if0_41022690";
$password = "6122005ouko";

// IMPORTANT: Replace 'if0_41022690_XXX' with the full name from your 'List of MySQL Databases' table.
$dbname = "if0_41022690_government_portal"; 

// Connect using the credentials above
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $db_connection_error = "Connection failed: " . $conn->connect_error;
    die($db_connection_error);
}
?>