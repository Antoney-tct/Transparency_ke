<?php
$servername = "localhost";
$username = "root"; // Update with your DB username
$password = "";     // Update with your DB password
$dbname = "government_portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $db_connection_error = "Connection failed: " . $conn->connect_error;
    die($db_connection_error);
}
?>