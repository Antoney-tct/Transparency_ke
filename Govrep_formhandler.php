<?php

header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Include database connection
require_once 'db_connect.php';

// Check if there was a database connection error
if (isset($db_connection_error) && !empty($db_connection_error)) {
    $response['message'] = $db_connection_error;
    echo json_encode($response);
    exit;
}

// Check if the connection object exists
if (!isset($conn) || !$conn) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Validate and sanitize input data
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$employeeId = trim(filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$department = trim(filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING));
$position = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING));
$region = trim(filter_input(INPUT_POST, 'region', FILTER_SANITIZE_STRING));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit;
}

// Validate password
if (strlen($password) < 8) {
    $response['message'] = 'Password must be at least 8 characters long';
    echo json_encode($response);
    exit;
}

// Check if passwords match
if ($password !== $confirmPassword) {
    $response['message'] = 'Passwords do not match';
    echo json_encode($response);
    exit;
}

try {
    // Check if email already exists
    $checkEmailSql = "SELECT id FROM government_representatives WHERE email = ?";
    $checkStmt = $conn->prepare($checkEmailSql);
    
    if (!$checkStmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $response['message'] = 'Email already registered';
        echo json_encode($response);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Check if employee ID already exists
    $checkIdSql = "SELECT id FROM government_representatives WHERE employee_id = ?";
    $checkStmt = $conn->prepare($checkIdSql);
    
    if (!$checkStmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    $checkStmt->bind_param("s", $employeeId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $response['message'] = 'Government ID already registered';
        echo json_encode($response);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if created_at column exists
    $checkColumnSql = "SHOW COLUMNS FROM government_representatives LIKE 'created_at'";
    $columnResult = $conn->query($checkColumnSql);
    $hasCreatedAt = ($columnResult && $columnResult->num_rows > 0);

    // Prepare SQL statement for insertion based on table structure
    if ($hasCreatedAt) {
        $sql = "INSERT INTO government_representatives (name, employee_id, email, department, position, region, password, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $name, $employeeId, $email, $department, $position, $region, $hashedPassword);
    } else {
        $sql = "INSERT INTO government_representatives (name, employee_id, email, department, position, region, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $name, $employeeId, $email, $department, $position, $region, $hashedPassword);
    }
    
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }

    // Execute the statement
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Registration successful';
        $response['email'] = $email;
    } else {
        throw new Exception('Registration failed: ' . $stmt->error);
    }

    // Close statement
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
    
    // Return JSON response
    echo json_encode($response);
}
