<?php
// Credentials now live in config.php, which is gitignored and never
// committed. If you're reading this on a fresh clone, copy
// config.example.php to config.php and fill in your real values.

$configPath = __DIR__ . '/config.php';

if (!file_exists($configPath)) {
    $db_connection_error = "Missing config.php. Copy config.example.php to config.php and add your real database credentials.";
    die($db_connection_error);
}

$config = require $configPath;

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);

if ($conn->connect_error) {
    $db_connection_error = "Connection failed: " . $conn->connect_error;
    die($db_connection_error);
}
