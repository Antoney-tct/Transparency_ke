<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

if (empty($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$email = $_SESSION['user_email'];
$id = $_POST['id'] ?? 'all';

if ($id === 'all') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_email = ?");
    $stmt->bind_param("s", $email);
} else {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_email = ? AND id = ?");
    $stmt->bind_param("si", $email, (int)$id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
