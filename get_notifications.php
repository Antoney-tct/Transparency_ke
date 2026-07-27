<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

if (empty($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$email = $_SESSION['user_email'];

$stmt = $conn->prepare("SELECT id, type, reference_id, message, is_read, created_at FROM notifications WHERE recipient_email = ? ORDER BY created_at DESC LIMIT 30");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
$unread = 0;
while ($row = $result->fetch_assoc()) {
    if (!$row['is_read']) $unread++;
    $notifications[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'notifications' => $notifications, 'unread_count' => $unread]);
