<?php
require_once 'db_connect.php';

// Check connection
if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

// Process only GET requests
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Validate and sanitize email
    if (!isset($_GET['email']) || empty($_GET['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }
    
    $email = $conn->real_escape_string(trim($_GET['email']));
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Get inquiries for the user
    $sql = "SELECT i.id, i.subject, i.message, i.status, i.created_at 
            FROM inquiries i 
            WHERE i.user_email = ? 
            ORDER BY i.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $inquiries = [];
    
    while ($row = $result->fetch_assoc()) {
        // Get replies for this inquiry
        $inquiry_id = $row['id'];
        $replies = [];
        
        $reply_sql = "SELECT reply_message, replied_at FROM replies WHERE inquiry_id = ? ORDER BY replied_at ASC";
        $reply_stmt = $conn->prepare($reply_sql);
        $reply_stmt->bind_param("i", $inquiry_id);
        $reply_stmt->execute();
        $reply_result = $reply_stmt->get_result();
        
        while ($reply_row = $reply_result->fetch_assoc()) {
            $replies[] = $reply_row;
        }
        
        $reply_stmt->close();
        
        // Add replies to inquiry
        $row['replies'] = $replies;
        $inquiries[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'inquiries' => $inquiries]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>