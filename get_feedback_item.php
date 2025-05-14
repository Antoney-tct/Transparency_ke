<?php
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No feedback ID provided']);
    exit;
}

$id = $conn->real_escape_string($_GET['id']);
$sql = "SELECT * FROM feedback WHERE id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $feedback = $result->fetch_assoc();
    echo json_encode($feedback);
} else {
    echo json_encode(['error' => 'Feedback not found']);
}

$conn->close();
?>