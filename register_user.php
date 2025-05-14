<?php
// Set response header to JSON
header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Include database connection
require_once 'db_connect.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Validate and sanitize common input data
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$userType = trim(filter_input(INPUT_POST, 'userType', FILTER_SANITIZE_STRING));

// Validate required common fields
if (empty($name) || empty($email) || empty($password) || empty($userType)) {
    $response['message'] = 'All fields are required';
    echo json_encode($response);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit;
}

// Validate password match if confirmPassword is provided
if (isset($confirmPassword) && $password !== $confirmPassword) {
    $response['message'] = 'Passwords do not match';
    echo json_encode($response);
    exit;
}

// Validate password strength (at least 8 characters)
if (strlen($password) < 8) {
    $response['message'] = 'Password must be at least 8 characters long';
    echo json_encode($response);
    exit;
}

try {
    // Determine which table to use based on user type
    if ($userType === 'citizen') {
        $table = 'citizens';
        
        // Get citizen-specific fields
        $nationalId = trim(filter_input(INPUT_POST, 'nationalId', FILTER_SANITIZE_STRING));
        $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
        
        // Validate citizen-specific required fields
        if (empty($nationalId) || empty($phone)) {
            $response['message'] = 'National ID and Phone are required';
            echo json_encode($response);
            exit;
        }
    } 
    else if ($userType === 'government') {
        $table = 'government_representatives';
        
        // Get government-specific fields
        $department = trim(filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING));
        $employeeId = trim(filter_input(INPUT_POST, 'employeeId', FILTER_SANITIZE_STRING));
        $position = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING));
        
        // Validate government-specific required fields
        if (empty($department) || empty($employeeId) || empty($position)) {
            $response['message'] = 'Department, Employee ID, and Position are required';
            echo json_encode($response);
            exit;
        }
    } 
    else {
        $response['message'] = 'Invalid user type';
        echo json_encode($response);
        exit;
    }
    
    // Check if email already exists in the appropriate table
    $checkSql = "SELECT id FROM $table WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $response['message'] = 'Email already exists';
        echo json_encode($response);
        $checkStmt->close();
        exit;
    }
    
    $checkStmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare SQL statement for insertion based on user type
    if ($userType === 'citizen') {
        $sql = "INSERT INTO citizens (name, email, password, national_id, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        
        $stmt->bind_param("sssss", $name, $email, $hashedPassword, $nationalId, $phone);
    } 
    else if ($userType === 'government') {
        $sql = "INSERT INTO government_representatives (name, email, password, department, employee_id, position) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        
        $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $department, $employeeId, $position);
    }
    
    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    // Set success response
    $response['success'] = true;
    $response['message'] = 'Registration successful!';
    $response['email'] = $email; // Return email for auto-fill on login page
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = 'Registration failed: ' . $e->getMessage();
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
    
    // Return JSON response
    echo json_encode($response);
}
