<?php
require_once 'db_connect.php';
// Check connection
if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}


// Database connection
$conn = new mysqli("localhost", "root", "", "government_portal");


// Set headers for JSON response
header('Content-Type: application/json');

// Check if email parameter is provided
if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email parameter is required'
    ]);
    exit;
}

$email = $_GET['email'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Get inquiries for the user
$stmt = $conn->prepare("SELECT id, subject, message, status, created_at FROM inquiries WHERE user_email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$inquiries = [];
while ($row = $result->fetch_assoc()) {
    // Get replies for this inquiry
    $inquiry_id = $row['id'];
    $replies_stmt = $conn->prepare("SELECT reply_message, replied_at FROM replies WHERE inquiry_id = ? ORDER BY replied_at ASC");
    $replies_stmt->bind_param("i", $inquiry_id);
    $replies_stmt->execute();
    $replies_result = $replies_stmt->get_result();
    
    $replies = [];
    while ($reply_row = $replies_result->fetch_assoc()) {
        $replies[] = $reply_row;
    }
    
    $row['replies'] = $replies;
    $inquiries[] = $row;
    
    $replies_stmt->close();
}

echo json_encode([
    'success' => true,
    'inquiries' => $inquiries
]);

$stmt->close();
$conn->close();
?>