<?php
require_once 'db_connect.php';

// Check connection
if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

// Process only POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $subject = $conn->real_escape_string(trim($_POST['subject']));
    $message = $conn->real_escape_string(trim($_POST['message']));
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if all required fields are provided
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Institution is optional in the request; if provided it must be a real row.
    $institutionId = isset($_POST['institution_id']) && $_POST['institution_id'] !== ''
        ? (int)$_POST['institution_id']
        : null;

    // Insert into database
    $sql = "INSERT INTO inquiries (user_name, user_email, subject, message, status, created_at, institution_id) 
            VALUES (?, ?, ?, ?, 'new', NOW(), ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $subject, $message, $institutionId);
    
    if ($stmt->execute()) {
        $inquiryId = $stmt->insert_id;

        // Log the opening message into the thread
        $msgStmt = $conn->prepare("INSERT INTO messages (inquiry_id, sender_type, sender_name, body) VALUES (?, 'citizen', ?, ?)");
        $msgStmt->bind_param("iss", $inquiryId, $name, $message);
        $msgStmt->execute();
        $msgStmt->close();

        // Notify staff at the target institution (or all reps if unrouted)
        if ($institutionId) {
            $notifSql = "INSERT INTO notifications (recipient_email, type, reference_id, message)
                         SELECT email, 'new_inquiry', ?, ? FROM government_representatives
                         WHERE institution_id = ? AND status = 'approved'";
            $notifText = "New inquiry: " . $subject;
            $notif = $conn->prepare($notifSql);
            $notif->bind_param("isi", $inquiryId, $notifText, $institutionId);
            $notif->execute();
            $notif->close();
        }

        echo json_encode(['success' => true, 'message' => 'Inquiry submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>