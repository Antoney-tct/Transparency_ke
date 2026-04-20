<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$inquiry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get inquiry details
$query = "SELECT * FROM inquiries WHERE id = '$inquiry_id'";
$result = $conn->query($query);
$inquiry = $result->fetch_assoc();

// Get replies
$query = "SELECT * FROM replies WHERE inquiry_id = '$inquiry_id' ORDER BY replied_at ASC";
$result = $conn->query($query);
$replies = [];
while ($row = $result->fetch_assoc()) {
    $replies[] = $row;
}

$inquiry['replies'] = $replies;
echo json_encode($inquiry);
$conn->close();
?>