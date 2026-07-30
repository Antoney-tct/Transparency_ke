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

if (empty($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['avatar'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error.']);
    exit;
}

// 3MB limit — plenty for a profile photo, small enough not to strain
// InfinityFree's free-tier limits
if ($file['size'] > 3 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Image must be under 3MB.']);
    exit;
}

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$mime = mime_content_type($file['tmp_name']);
if (!isset($allowed[$mime])) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, or WEBP images are allowed.']);
    exit;
}
$ext = $allowed[$mime];

$userType = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];
$table = $userType === 'citizen' ? 'citizens' : 'government_representatives';

$uploadDir = __DIR__ . '/uploads/avatars/';
$filename = $userType . '_' . $userId . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $filename;
$publicPath = 'uploads/avatars/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'message' => 'Could not save the file.']);
    exit;
}

// Remove the old avatar file so uploads don't accumulate forever
$oldPathStmt = $conn->prepare("SELECT avatar_path FROM $table WHERE id = ?");
$oldPathStmt->bind_param("i", $userId);
$oldPathStmt->execute();
$oldPathStmt->bind_result($oldPath);
$oldPathStmt->fetch();
$oldPathStmt->close();

$stmt = $conn->prepare("UPDATE $table SET avatar_path = ? WHERE id = ?");
$stmt->bind_param("si", $publicPath, $userId);

if ($stmt->execute()) {
    if ($oldPath && $oldPath !== $publicPath && file_exists(__DIR__ . '/' . $oldPath)) {
        @unlink(__DIR__ . '/' . $oldPath);
    }
    echo json_encode(['success' => true, 'avatar_path' => $publicPath]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
