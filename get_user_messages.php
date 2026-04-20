<?php

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated. Please log in.'
    ]);
    exit;
}

// Include centralized DB connection
require_once 'db_connect.php';
if (isset($db_connection_error)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

// Get the logged-in user's email from session
$email = $_SESSION['user_email'];

// Fetch messages and replies
$stmt = $conn->prepare("
    SELECT 
        i.id, 
        i.name, 
        i.email, 
        i.subject, 
        i.message, 
        i.status, 
        i.created_at, 
        r.reply_message, 
        r.created_at as replied_at
    FROM inquiries i
    LEFT JOIN replies r ON i.id = r.inquiry_id
    WHERE i.email = ?
    ORDER BY i.created_at DESC
");

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();
$conn->close();

// Return messages
echo json_encode([
    'status' => 'success',
    'messages' => $messages
]);
?>