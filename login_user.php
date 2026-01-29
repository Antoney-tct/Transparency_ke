<?php
header('Content-Type: application/json');
session_start(); //  store session data

require_once 'db_connect.php';

$response = ['success' => false, 'message' => 'Invalid login attempt.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
        if (!$user_found) {
            $sql_gov = "SELECT id, name, password FROM government_representatives WHERE email = ?";
            if ($stmt_gov = $conn->prepare($sql_gov)) {
                $stmt_gov->bind_param("s", $email);
                $stmt_gov->execute();
                $stmt_gov->store_result();

                if ($stmt_gov->num_rows == 1) {
                    $stmt_gov->bind_result($id, $name, $hashed_password);
                    if ($stmt_gov->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct for government rep
                            $user_found = true;
                            $user_id = $id;
                            $user_name = $name;
                            $user_type = 'government';
                            $redirect_url = 'gov-rep-dashboard.html'; 
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

            $response['success'] = true;
            $response['message'] = 'Login successful! Redirecting...';
            $response['redirectUrl'] = $redirect_url; // Send redirect URL to JS
        } else {
            // Login failed (email not found or password incorrect)
            $response['message'] = 'Invalid email or password.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>
