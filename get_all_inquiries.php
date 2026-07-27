<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// This endpoint used to have NO auth check at all — any visitor could pull
// every citizen inquiry (names, emails, messages) by hitting the URL
// directly. Now it requires a logged-in, approved government rep.
if (empty($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'government') {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Platform admins see everything; regular reps only see inquiries
    // routed to their own institution.
    $isAdmin = !empty($_SESSION['is_platform_admin']);
    $institutionId = $_SESSION['institution_id'] ?? null;

    $sql = "SELECT i.id, i.user_name, i.user_email, i.subject, i.message, i.status, i.created_at, i.institution_id,
                   inst.name AS institution_name
            FROM inquiries i
            LEFT JOIN institutions inst ON inst.id = i.institution_id";
    if (!$isAdmin) {
        $sql .= " WHERE i.institution_id = ? OR i.institution_id IS NULL";
    }
    $sql .= " ORDER BY
                CASE
                    WHEN i.status = 'new' THEN 1
                    WHEN i.status = 'replied' THEN 2
                    WHEN i.status = 'closed' THEN 3
                END,
                i.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$isAdmin) {
        $stmt->bind_param("i", $institutionId);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $inquiries = [];
        while ($row = $result->fetch_assoc()) {
            $inquiry_id = $row['id'];
            $thread = [];

            $msgStmt = $conn->prepare("SELECT sender_type, sender_name, body, created_at FROM messages WHERE inquiry_id = ? ORDER BY created_at ASC");
            $msgStmt->bind_param("i", $inquiry_id);
            $msgStmt->execute();
            $msgResult = $msgStmt->get_result();
            while ($msgRow = $msgResult->fetch_assoc()) {
                $thread[] = $msgRow;
            }
            $msgStmt->close();

            $row['messages'] = $thread;
            $inquiries[] = $row;
        }
        echo json_encode(['success' => true, 'inquiries' => $inquiries]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error retrieving inquiries: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
