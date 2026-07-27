<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// This endpoint previously trusted a plain ?email= query parameter —
// anyone could type in any citizen's email and read their private
// correspondence with government, replies included. Now it only ever
// returns inquiries belonging to the currently logged-in citizen.
if (empty($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'citizen') {
    echo json_encode(['success' => false, 'message' => 'Please log in to view your inquiries.']);
    exit;
}

if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

$email = $_SESSION['user_email'];

$sql = "SELECT i.id, i.subject, i.status, i.created_at, inst.name AS institution_name
        FROM inquiries i
        LEFT JOIN institutions inst ON inst.id = i.institution_id
        WHERE i.user_email = ?
        ORDER BY i.created_at DESC";
$stmt = $conn->prepare($sql);
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
    $inquiry['messages'] = [];
    while ($msgRow = $msgResult->fetch_assoc()) {
        $inquiry['messages'][] = $msgRow;
    }
    $msgStmt->close();
}
unset($inquiry);

$conn->close();
echo json_encode(['success' => true, 'inquiries' => $inquiries]);
