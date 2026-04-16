<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$sql = "SELECT id, first_name as first, last_name as last, email, role, county, joined, last_active as lastActive, status, color FROM users ORDER BY joined DESC";
$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode(['success' => true, 'users' => $users]);
$conn->close();
?>