<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['loggedin'])) {
    echo json_encode(['loggedin' => false]);
    exit;
}

echo json_encode([
    'loggedin' => true,
    'user_type' => $_SESSION['user_type'] ?? null,
    'user_name' => $_SESSION['user_name'] ?? null,
    'user_email' => $_SESSION['user_email'] ?? null,
    'is_platform_admin' => !empty($_SESSION['is_platform_admin']),
]);
