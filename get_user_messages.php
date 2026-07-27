<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated. Please log in.']);
    exit;
}

require_once 'db_connect.php';
if (isset($db_connection_error)) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$email = $_SESSION['user_email'];

// Pull each inquiry the citizen opened, plus its full message thread —
// not just a single reply. This is a real conversation now.
$stmt = $conn->prepare("
    SELECT i.id, i.subject, i.status, i.created_at, inst.name AS institution_name
    FROM inquiries i
    LEFT JOIN institutions inst ON inst.id = i.institution_id
    WHERE i.user_email = ?
    ORDER BY i.created_at DESC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$inquiries = [];
while ($row = $result->fetch_assoc()) {
    $inquiries[] = $row;
}
$stmt->close();

foreach ($inquiries as &$inquiry) {
    $msgStmt = $conn->prepare("SELECT sender_type, sender_name, body, created_at FROM messages WHERE inquiry_id = ? ORDER BY created_at ASC");
    $msgStmt->bind_param("i", $inquiry['id']);
    $msgStmt->execute();
    $msgResult = $msgStmt->get_result();
    $inquiry['thread'] = [];
    while ($msgRow = $msgResult->fetch_assoc()) {
        $inquiry['thread'][] = $msgRow;
    }
    $msgStmt->close();
}
unset($inquiry);

$conn->close();

echo json_encode(['status' => 'success', 'messages' => $inquiries]);
