<?php
// register_user.php
header('Content-Type: application/json');

// Basic response structure
$response = ['success' => false, 'message' => ''];

// Include centralized DB connection (db_connect.php should set $conn or $db_connection_error)
require_once 'db_connect.php';

// Verify DB connection
if (isset($db_connection_error) && !empty($db_connection_error)) {
    $response['message'] = 'Database connection failed.';
    echo json_encode($response);
    exit;
}
if (!isset($conn) || !$conn) {
    $response['message'] = 'Database connection failed.';
    echo json_encode($response);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Helper to safely fetch POST values
function post_str($key) {
    return trim((string)filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING) ?? '');
}

// Common inputs
$name = post_str('name');
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$userType = post_str('userType');

// Basic required fields
if ($name === '' || $email === '' || $password === '' || $userType === '') {
    $response['message'] = 'All required fields must be provided.';
    echo json_encode($response);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format.';
    echo json_encode($response);
    exit;
}

// Confirm passwords match
if ($confirmPassword !== '' && $password !== $confirmPassword) {
    $response['message'] = 'Passwords do not match.';
    echo json_encode($response);
    exit;
}

// Strong password policy (server-side)
$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{10,}$/';
if (!preg_match($passwordRegex, $password)) {
    $response['message'] = 'Password must be at least 10 characters and include uppercase, lowercase, number and special character.';
    echo json_encode($response);
    exit;
}

try {
    // Normalize user type
    $userType = strtolower($userType);

    // Gather type-specific fields and validate
    if ($userType === 'citizen') {
        $nationalId = post_str('nationalId');
        $phone = post_str('phone');

        if ($nationalId === '' || $phone === '') {
            $response['message'] = 'National ID and Phone are required for citizens.';
            echo json_encode($response);
            exit;
        }

        // Optional: validate national ID length/format here
    } elseif ($userType === 'government' || $userType === 'gov' || $userType === 'government_representative') {
        // government-specific
        $department = post_str('department');
        $employeeId = post_str('employeeId');
        $position = post_str('position');
        $region = post_str('region');

        if ($department === '' || $employeeId === '' || $position === '' || $region === '') {
            $response['message'] = 'Department, Employee ID, Position and Region are required for government representatives.';
            echo json_encode($response);
            exit;
        }

        // Employee ID format (8-15 alphanumeric)
        if (!preg_match('/^[A-Z0-9]{8,15}$/i', $employeeId)) {
            $response['message'] = 'Government ID must be 8-15 alphanumeric characters.';
            echo json_encode($response);
            exit;
        }

        // Enforce .gov.ke email domain (server-side)
        if (!preg_match('/\.gov\.ke$/i', $email)) {
            $response['message'] = 'Government staff email must end with .gov.ke';
            echo json_encode($response);
            exit;
        }
    } else {
        $response['message'] = 'Invalid user type.';
        echo json_encode($response);
        exit;
    }

    // -- Prevent duplicate email across both user tables --
    $exists = false;

    $checkCitizens = $conn->prepare("SELECT id FROM citizens WHERE email = ?");
    if ($checkCitizens) {
        $checkCitizens->bind_param("s", $email);
        $checkCitizens->execute();
        $checkCitizens->store_result();
        if ($checkCitizens->num_rows > 0) $exists = true;
        $checkCitizens->close();
    } else {
        error_log('register_user: prepare citizens check failed: ' . $conn->error);
    }

    $checkGov = $conn->prepare("SELECT id FROM government_representatives WHERE email = ?");
    if ($checkGov) {
        $checkGov->bind_param("s", $email);
        $checkGov->execute();
        $checkGov->store_result();
        if ($checkGov->num_rows > 0) $exists = true;
        $checkGov->close();
    } else {
        error_log('register_user: prepare gov check failed: ' . $conn->error);
    }

    if ($exists) {
        $response['message'] = 'Email already exists.';
        echo json_encode($response);
        exit;
    }

    // If government user, also ensure employeeId is unique
    if ($userType === 'government') {
        $chkEmp = $conn->prepare("SELECT id FROM government_representatives WHERE employee_id = ?");
        if ($chkEmp) {
            $chkEmp->bind_param("s", $employeeId);
            $chkEmp->execute();
            $chkEmp->store_result();
            if ($chkEmp->num_rows > 0) {
                $chkEmp->close();
                $response['message'] = 'Employee ID already exists.';
                echo json_encode($response);
                exit;
            }
            $chkEmp->close();
        } else {
            error_log('register_user: prepare employeeId check failed: ' . $conn->error);
        }
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into appropriate table
    if ($userType === 'citizen') {
        $sql = "INSERT INTO citizens (name, email, password, national_id, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $stmt->bind_param("sssss", $name, $email, $hashedPassword, $nationalId, $phone);
    } else { // government
        // Ensure the government_representatives table has a 'region' column
        $sql = "INSERT INTO government_representatives (name, email, password, department, employee_id, position, region) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $stmt->bind_param("sssssss", $name, $email, $hashedPassword, $department, $employeeId, $position, $region);
    }

    if (!$stmt->execute()) {
        // If a duplicate key error occurs (just in case), give a friendly message
        $err = $stmt->error;
        error_log('register_user: execute error: ' . $err);
        throw new Exception('Database execution error.');
    }

    $stmt->close();

    // Success
    $response['success'] = true;
    $response['message'] = 'Registration successful!';
    $response['email'] = $email;

} catch (Exception $e) {
    // Log details for server-side debugging; don't expose internals to clients
    error_log('register_user exception: ' . $e->getMessage());
    $response['message'] = 'Registration failed due to a server error. Please try again later.';
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
    echo json_encode($response);
}
?>
