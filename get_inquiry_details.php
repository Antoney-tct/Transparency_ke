<?php
header('Content-Type: application/json');

// Database connection
$db = new mysqli('localhost', 'username', 'password', 'government_portal');

if ($db->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$inquiry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get inquiry details
$query = "SELECT * FROM inquiries WHERE id = '$inquiry_id'";
$result = $db->query($query);
$inquiry = $result->fetch_assoc();

// Get replies
$query = "SELECT * FROM replies WHERE inquiry_id = '$inquiry_id' ORDER BY replied_at ASC";
$result = $db->query($query);
$replies = [];
while ($row = $result->fetch_assoc()) {
    $replies[] = $row;
}

$inquiry['replies'] = $replies;
echo json_encode($inquiry);
$db->close();
?>