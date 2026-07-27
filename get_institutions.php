<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

// Public list — citizens need this to route an inquiry, government
// signups need it to attach to an existing institution instead of
// silently creating a duplicate.
$result = $conn->query("SELECT id, name, type, region, verified FROM institutions ORDER BY name ASC");

$institutions = [];
while ($row = $result->fetch_assoc()) {
    $institutions[] = $row;
}

echo json_encode(['success' => true, 'institutions' => $institutions]);
$conn->close();
