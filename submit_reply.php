<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// This previously had no auth check — any POST request could insert a
// reply as "the government". Now requires an approved, logged-in rep.
if (empty($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'government') {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['inquiry_id']) || !isset($_POST['reply_message']) ||
        empty($_POST['inquiry_id']) || empty($_POST['reply_message'])) {
        echo json_encode(['success' => false, 'message' => 'Inquiry ID and reply message are required']);
        exit;
    }

    $inquiry_id = (int)$_POST['inquiry_id'];
    $reply_message = trim($_POST['reply_message']);
    $repEmail = $_SESSION['user_email'];
    $repName = $_SESSION['user_name'] ?? 'Government Representative';
    $isAdmin = !empty($_SESSION['is_platform_admin']);
    $repInstitutionId = $_SESSION['institution_id'] ?? null;

    // A regular rep may only answer inquiries routed to their own institution
    if (!$isAdmin) {
        $check = $conn->prepare("SELECT institution_id FROM inquiries WHERE id = ?");
        $check->bind_param("i", $inquiry_id);
        $check->execute();
        $check->bind_result($inquiryInstitutionId);
        $check->fetch();
        $check->close();

        if ($inquiryInstitutionId !== null && $inquiryInstitutionId != $repInstitutionId) {
            echo json_encode(['success' => false, 'message' => 'This inquiry belongs to a different institution.']);
            exit;
        }
    }

    $conn->begin_transaction();

    try {
        $msgStmt = $conn->prepare("INSERT INTO messages (inquiry_id, sender_type, sender_name, body) VALUES (?, 'gov_rep', ?, ?)");
        $msgStmt->bind_param("iss", $inquiry_id, $repName, $reply_message);
        $msgStmt->execute();
        $msgStmt->close();

        $statusStmt = $conn->prepare("UPDATE inquiries SET status = 'replied' WHERE id = ?");
        $statusStmt->bind_param("i", $inquiry_id);
        $statusStmt->execute();
        $statusStmt->close();

        // Notify the citizen who opened the inquiry
        $citizenStmt = $conn->prepare("SELECT user_email, subject FROM inquiries WHERE id = ?");
        $citizenStmt->bind_param("i", $inquiry_id);
        $citizenStmt->execute();
        $citizenStmt->bind_result($citizenEmail, $subject);
        $hasCitizen = $citizenStmt->fetch();
        $citizenStmt->close(); // must close before the next prepare on $conn

        if ($hasCitizen) {
            $notifText = "New reply on: " . $subject;
            $notif = $conn->prepare("INSERT INTO notifications (recipient_email, type, reference_id, message) VALUES (?, 'new_reply', ?, ?)");
            $notif->bind_param("sis", $citizenEmail, $inquiry_id, $notifText);
            $notif->execute();
            $notif->close();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
