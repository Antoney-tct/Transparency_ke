<?php
require_once 'db_connect.php';

// Check connection
if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

// Process only POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    if (!isset($_POST['inquiry_id']) || !isset($_POST['status']) || 
        empty($_POST['inquiry_id']) || empty($_POST['status'])) {
        echo json_encode(['success' => false, 'message' => 'Inquiry ID and status are required']);
        exit;
    }
    
    $inquiry_id = (int)$_POST['inquiry_id'];
    $status = $conn->real_escape_string(trim($_POST['status']));
    
    // Validate status
    $valid_statuses = ['new', 'replied', 'closed'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit;
    }
    
    // Update inquiry status
    $sql = "UPDATE inquiries SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $inquiry_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>