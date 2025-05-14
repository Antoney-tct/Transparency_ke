<?php
require_once 'db_connect.php';

// Check connection
if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

// Process only GET requests
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get all inquiries for admin
    $sql = "SELECT i.id, i.user_name, i.user_email, i.subject, i.message, i.status, i.created_at 
            FROM inquiries i 
            ORDER BY 
                CASE 
                    WHEN i.status = 'new' THEN 1
                    WHEN i.status = 'replied' THEN 2
                    WHEN i.status = 'closed' THEN 3
                END, 
                i.created_at DESC";
    
    $result = $conn->query($sql);
    
    if ($result) {
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
        
        echo json_encode(['success' => true, 'inquiries' => $inquiries]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error retrieving inquiries: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>