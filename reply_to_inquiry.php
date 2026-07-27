<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (empty($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'citizen') {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$inquiry_id = (int)($_POST['inquiry_id'] ?? 0);
$body = trim($_POST['message'] ?? '');
$citizenEmail = $_SESSION['user_email'];
$citizenName = $_SESSION['user_name'] ?? 'Citizen';

if (!$inquiry_id || $body === '') {
    echo json_encode(['success' => false, 'message' => 'Message and inquiry id are required.']);
    exit;
}

// Citizens can only reply to their own inquiries
$check = $conn->prepare("SELECT user_email, institution_id FROM inquiries WHERE id = ?");
$check->bind_param("i", $inquiry_id);
$check->execute();
$check->bind_result($ownerEmail, $institutionId);
$check->fetch();
$check->close();

if ($ownerEmail !== $citizenEmail) {
    echo json_encode(['success' => false, 'message' => 'This inquiry does not belong to you.']);
    exit;
}

$msgStmt = $conn->prepare("INSERT INTO messages (inquiry_id, sender_type, sender_name, body) VALUES (?, 'citizen', ?, ?)");
$msgStmt->bind_param("iss", $inquiry_id, $citizenName, $body);

if ($msgStmt->execute()) {
    // Re-open the inquiry so it surfaces back in the rep's queue
    $reopen = $conn->prepare("UPDATE inquiries SET status = 'new' WHERE id = ?");
    $reopen->bind_param("i", $inquiry_id);
    $reopen->execute();
    $reopen->close();

    // Notify the relevant institution's reps
    if ($institutionId) {
        $notifText = "Citizen follow-up on inquiry #" . $inquiry_id;
        $notif = $conn->prepare("INSERT INTO notifications (recipient_email, type, reference_id, message)
                                  SELECT email, 'new_inquiry', ?, ? FROM government_representatives
                                  WHERE institution_id = ? AND status = 'approved'");
        $notif->bind_param("isi", $inquiry_id, $notifText, $institutionId);
        $notif->execute();
        $notif->close();
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$msgStmt->close();
$conn->close();
