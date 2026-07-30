<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (empty($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

$userType = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];

if ($userType === 'citizen') {
    $stmt = $conn->prepare("SELECT name, email, phone, national_id, county, gender, dob, headline, about, skills, avatar_path, profile_completed FROM citizens WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Profile not found.']);
        exit;
    }

    // Real submission counts, not the fake hardcoded stats the old page shipped with
    $inqStmt = $conn->prepare("SELECT COUNT(*) FROM inquiries WHERE user_email = ?");
    $inqStmt->bind_param("s", $row['email']);
    $inqStmt->execute();
    $inqStmt->bind_result($inquiryCount);
    $inqStmt->fetch();
    $inqStmt->close();

    $row['skills'] = $row['skills'] ? json_decode($row['skills']) : [];
    $row['stats'] = ['inquiries' => (int)$inquiryCount];
    echo json_encode(['success' => true, 'profile' => $row]);

} else { // government
    $stmt = $conn->prepare("
        SELECT gr.name, gr.email, gr.phone, gr.employee_id AS staffid, gr.position AS designation,
               gr.region, gr.since_year, gr.about, gr.avatar_path, gr.profile_completed,
               i.name AS ministry
        FROM government_representatives gr
        LEFT JOIN institutions i ON i.id = gr.institution_id
        WHERE gr.id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Profile not found.']);
        exit;
    }

    $repliesStmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE sender_type = 'gov_rep' AND sender_name = ?");
    $repliesStmt->bind_param("s", $row['name']);
    $repliesStmt->execute();
    $repliesStmt->bind_result($replyCount);
    $repliesStmt->fetch();
    $repliesStmt->close();

    $row['stats'] = ['reports' => (int)$replyCount];
    echo json_encode(['success' => true, 'profile' => $row]);
}

$conn->close();
