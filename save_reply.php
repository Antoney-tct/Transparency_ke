<?php
// Include the database connection file
require_once 'db_connect.php'; // Ensure this file exists and connects properly

// Check if there was a database connection error
if (isset($db_connection_error) && !empty($db_connection_error)) {
    $response = ['status' => 'error', 'message' => $db_connection_error];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Initialize response array
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

// Set header to JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Check if required POST parameters are set
if (
    !isset($_POST['feedback_id']) ||
    !isset($_POST['status']) ||
    !isset($_POST['public_response'])
) {
    $response['message'] = 'Missing required parameters.';
    echo json_encode($response);
    exit;
}

// Get data from POST and sanitize using the connection object.
$feedbackId = $conn->real_escape_string($_POST['feedback_id']);
$status = $conn->real_escape_string($_POST['status']);
$publicResponse = $conn->real_escape_string($_POST['public_response']);
$internalNotes = isset($_POST['internal_notes']) ? $conn->real_escape_string($_POST['internal_notes']) : '';

// --- Database Interaction (using prepared statements for security) ---
// Update the 'inquiries' table first (to change status)
$sql = "UPDATE inquiries SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $response['message'] = 'Prepare statement failed: ' . $conn->error;
} else {
    // Bind parameters
    $stmt->bind_param("si", $status, $feedbackId);

    // Execute UPDATE
    if ($stmt->execute()) {
       // Now insert the reply into 'replies' table
       $insertSql = "INSERT INTO replies (inquiry_id, reply_message, responded_at,internal_notes) VALUES (?, ?, NOW(),?)";
       $insertStmt = $conn->prepare($insertSql);

       if ($insertStmt === false) {
           $response['message'] = 'Prepare statement failed: ' . $conn->error;
       } else {
           // Bind parameters
           $insertStmt->bind_param("iss", $feedbackId, $publicResponse,$internalNotes);

           // Execute INSERT
           if ($insertStmt->execute()) {
               $response = ['status' => 'success', 'message' => 'Reply saved successfully.'];
           } else {
               $response['message'] = 'Error inserting reply: ' . $insertStmt->error;
           }
       }
         if (isset($insertStmt) && $insertStmt !== false) {
            $insertStmt->close();
        }
    } else {
        $response['message'] = 'Error updating inquiry status: ' . $stmt->error;
    }
    $stmt->close();
}

// Close the connection
$conn->close();

// Send the JSON response
echo json_encode($response);
exit;

?>
