<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// Only a logged-in platform admin may approve/reject or list pending reps
if (empty($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'government' || empty($_SESSION['is_platform_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'list_pending') {
    $sql = "SELECT gr.id, gr.name, gr.email, gr.employee_id, gr.position, gr.region, gr.registration_date,
                   i.name AS institution_name, i.verified AS institution_verified
            FROM government_representatives gr
            LEFT JOIN institutions i ON i.id = gr.institution_id
            WHERE gr.status = 'pending'
            ORDER BY gr.registration_date ASC";
    $result = $conn->query($sql);
    $pending = [];
    while ($row = $result->fetch_assoc()) {
        $pending[] = $row;
    }
    echo json_encode(['success' => true, 'pending' => $pending]);

} elseif ($action === 'approve' || $action === 'reject') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing rep id.']);
        exit;
    }
    $newStatus = $action === 'approve' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE government_representatives SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);

    if ($stmt->execute()) {
        // Notify the rep so the UI has something real to show, not decoration
        $emailStmt = $conn->prepare("SELECT email, institution_id FROM government_representatives WHERE id = ?");
        $emailStmt->bind_param("i", $id);
        $emailStmt->execute();
        $emailStmt->bind_result($repEmail, $institutionId);
        if ($emailStmt->fetch()) {
            $notifType = $action === 'approve' ? 'account_approved' : 'account_rejected';
            $notifMsg = $action === 'approve' ? 'Your government account was approved.' : 'Your government account registration was declined.';
            $notif = $conn->prepare("INSERT INTO notifications (recipient_email, type, reference_id, message) VALUES (?, ?, ?, ?)");
            $notif->bind_param("ssis", $repEmail, $notifType, $id, $notifMsg);
            $notif->execute();
            $notif->close();

            // First approval for an institution also marks it verified
            if ($action === 'approve' && $institutionId) {
                $verify = $conn->prepare("UPDATE institutions SET verified = 1 WHERE id = ?");
                $verify->bind_param("i", $institutionId);
                $verify->execute();
                $verify->close();
            }
        }
        $emailStmt->close();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    $stmt->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

$conn->close();
