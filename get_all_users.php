<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

// Select both last_active and last_login. Use last_login primarily.
$sql = "SELECT 
            id, first_name AS first, last_name AS last, email, national_id, phone,
            role, category, county, joined, status, color, 
            last_active, last_login 
        FROM users ORDER BY id DESC";

$result = $conn->query($sql);

$users = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        // Fallback for empty login dates to keep the UI clean
        if (empty($row['last_login']) || $row['last_login'] == '0000-00-00 00:00:00') {
            // Use last_active if last_login is missing
            $row['last_login'] = !empty($row['last_active']) ? $row['last_active'] : 'Never';
        }
        $users[] = $row;
    }
    echo json_encode(['success' => true, 'users' => $users]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database query failed: ' . $conn->error
    ]);
}

$conn->close();
?>