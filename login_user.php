<?php
header('Content-Type: application/json');
session_start(); //  store session data

$response = ['success' => false, 'message' => 'Invalid login attempt.'];

// Set a short timeout for the database connection attempt
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        require_once 'db_connect.php';
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
        if (strpos($error_msg, 'timed out') !== false) {
            $error_msg = "The database server is taking too long to respond. Please check your credentials in db_connect.php.";
        }
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $error_msg]);
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    

    if (empty($email) || empty($password)) {
        $response['message'] = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
    } else {
        $user_found = false;
        $user_id = null;
        $user_name = null;
        $user_type = null;
        $redirect_url = null;

        try {
        // --- Try logging in as a Citizen ---
        $sql_citizen = "SELECT id, name, password FROM citizens WHERE email = ?";
        if ($stmt_citizen = $conn->prepare($sql_citizen)) {
            $stmt_citizen->bind_param("s", $email);
            $stmt_citizen->execute();
            $stmt_citizen->store_result();

            if ($stmt_citizen->num_rows == 1) {
                $stmt_citizen->bind_result($id, $name, $hashed_password);
                if ($stmt_citizen->fetch()) {
                    if (password_verify($password, $hashed_password)) {
                        // Password is correct for citizen
                        $user_found = true;
                        $user_id = $id;
                        $user_name = $name;
                        $user_type = 'citizen';
                        $redirect_url = 'user_dashboard.html'; 
                    }
                }
            }
            $stmt_citizen->close();
        } else {
             // Log error: error_log("Citizen login prepare error: " . $conn->error);
             $response['message'] = 'Database error during login. Please try again later.';
             echo json_encode($response);
             $conn->close();
             exit;
        }

        // --- If not found as citizen, try logging in as Government Rep ---
        $gov_institution_id = null;
        $gov_is_admin = 0;
        $gov_status = null;

        if (!$user_found) {
            $sql_gov = "SELECT id, name, password, status, institution_id, is_platform_admin FROM government_representatives WHERE email = ?";
            if ($stmt_gov = $conn->prepare($sql_gov)) {
                $stmt_gov->bind_param("s", $email);
                $stmt_gov->execute();
                $stmt_gov->store_result();

                if ($stmt_gov->num_rows == 1) {
                    $stmt_gov->bind_result($id, $name, $hashed_password, $gov_status, $gov_institution_id, $gov_is_admin);
                    if ($stmt_gov->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            if ($gov_status === 'pending') {
                                $response['message'] = 'Your government account is still pending verification by a platform administrator.';
                                echo json_encode($response);
                                $stmt_gov->close();
                                $conn->close();
                                exit;
                            }
                            if ($gov_status === 'rejected') {
                                $response['message'] = 'This government account was not approved. Contact your institution administrator.';
                                echo json_encode($response);
                                $stmt_gov->close();
                                $conn->close();
                                exit;
                            }
                            // Password correct and account approved
                            $user_found = true;
                            $user_id = $id;
                            $user_name = $name;
                            $user_type = 'government';
                        $redirect_url = 'Addmin-projects.html'; 
                        }
                    }
                }
                $stmt_gov->close();
            } else {
                 // Log error: error_log("Gov Rep login prepare error: " . $conn->error);
                 $response['message'] = 'Database error during login. Please try again later.';
                 echo json_encode($response);
                 $conn->close();
                 exit;
            }
        }

        // --- Process Login Result ---
        if ($user_found) {
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user information in session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $user_type; // 'citizen' or 'government'
            if ($user_type === 'government') {
                $_SESSION['institution_id'] = $gov_institution_id;
                $_SESSION['is_platform_admin'] = (bool)$gov_is_admin;
            }

            // Safe Update: Check if 'last_login' column exists before updating it.
            // This dashboard-sync step is non-essential — the session above is
            // already set, so a failure here must not report a successful
            // login as failed.
            try {
                $check_col = $conn->query("SHOW COLUMNS FROM `users` LIKE 'last_login'");
                $sql_update = ($check_col && $check_col->num_rows > 0)
                    ? "UPDATE users SET last_login = NOW(), last_active = 'Just now' WHERE email = ?"
                    : "UPDATE users SET last_active = 'Just now' WHERE email = ?";

                if ($stmt_update = $conn->prepare($sql_update)) {
                    $stmt_update->bind_param("s", $email);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
            } catch (Throwable $syncError) {
                error_log('login_user: dashboard sync (non-fatal) failed: ' . $syncError->getMessage());
            }

            $response['success'] = true;
            $response['message'] = 'Login successful! Redirecting...';
            $response['redirectUrl'] = $redirect_url; // Send redirect URL to JS
        } else {
            // Login failed (email not found or password incorrect)
            $response['message'] = 'Invalid email or password.';
        }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>
