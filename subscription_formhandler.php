<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        // success message
        echo json_encode(["success" => true, "message" => "Thank you for subscribing!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email address"]);
    }
} else {
    // Not a POST request
    header("Location: index.html");
    exit;
}
?>
