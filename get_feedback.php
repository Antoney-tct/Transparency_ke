<?php
include 'db_connect.php';

header('Content-Type: application/json');

$sql = "SELECT * FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);

$feedback = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
}

echo json_encode($feedback);
$conn->close();
?>