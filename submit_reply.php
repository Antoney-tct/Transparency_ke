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
    if (!isset($_POST['inquiry_id']) || !isset($_POST['reply_message']) || 
        empty($_POST['inquiry_id']) || empty($_POST['reply_message'])) {
        echo json_encode(['success' => false, 'message' => 'Inquiry ID and reply message are required']);
        exit;
    }
    
    $inquiry_id = (int)$_POST['inquiry_id'];
    $reply_message = $conn->real_escape_string(trim($_POST['reply_message']));
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert reply
        $reply_sql = "INSERT INTO replies (inquiry_id, reply_message, replied_at) VALUES (?, ?, NOW())";
        $reply_stmt = $conn->prepare($reply_sql);
        $reply_stmt->bind_param("is", $inquiry_id, $reply_message);
        $reply_stmt->execute();
        $reply_stmt->close();
        
        // Update inquiry status to 'replied'
        $status_sql = "UPDATE inquiries SET status = 'replied' WHERE id = ?";
        $status_stmt = $conn->prepare($status_sql);
        $status_stmt->bind_param("i", $inquiry_id);
        $status_stmt->execute();
        $status_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>