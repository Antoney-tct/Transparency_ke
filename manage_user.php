<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'save') {
    $id = $_POST['id'] ?? null;
    $first = $conn->real_escape_string($_POST['first']);
    $last = $conn->real_escape_string($_POST['last']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    $county = $conn->real_escape_string($_POST['county']);
    $status = $conn->real_escape_string($_POST['status']);
    $color = $conn->real_escape_string($_POST['color']);

    if ($id) {
        // Update existing
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, role=?, county=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $first, $last, $email, $role, $county, $status, $id);
    } else {
        // Insert new
        $joined = date('M Y');
        $sql = "INSERT INTO users (first_name, last_name, email, role, county, status, color, joined, last_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Just now')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $first, $last, $email, $role, $county, $status, $color, $joined);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    $stmt->close();

} elseif ($action === 'delete') {
    $id = $_POST['id'];
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();

} elseif ($action === 'toggleStatus') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $sql = "UPDATE users SET status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
} elseif ($action === 'resetPassword') {
    $id = $_POST['id'];
    
    // 1. Get user details from the management table
    $stmt = $conn->prepare("SELECT email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $email = $user['email'];
        $role = $user['role'];
        $newHashed = password_hash("Transparency@123", PASSWORD_DEFAULT);

        // 2. Map role to the correct authentication table
        $table = ($role === 'Citizen') ? 'citizens' : 'government_representatives';

        $stmtUpdate = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
        $stmtUpdate->bind_param("ss", $newHashed, $email);
        
        if ($stmtUpdate->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        $stmtUpdate->close();
    } else {
        echo json_encode(['success' => false, 'message' => "User not found"]);
    }
}

$conn->close();
?>