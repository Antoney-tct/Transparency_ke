<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (empty($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

if (!empty($db_connection_error)) {
    echo json_encode(['success' => false, 'message' => $db_connection_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$userType = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];

function post($k, $default = null) {
    return isset($_POST[$k]) ? trim($_POST[$k]) : $default;
}

if ($userType === 'citizen') {
    // Whitelisted, editable fields only — never email or national_id here
    $name = post('name');
    $phone = post('phone');
    $county = post('county');
    $gender = post('gender');
    $dob = post('dob') ?: null;
    $headline = post('headline');
    $about = post('about');
    $skillsRaw = post('skills'); // expects a JSON array string from the client
    $skills = null;
    if ($skillsRaw !== null) {
        $decoded = json_decode($skillsRaw, true);
        $skills = is_array($decoded) ? json_encode(array_values(array_filter($decoded))) : null;
    }
    $markComplete = post('complete') === '1';
    $completeFlag = $markComplete ? 1 : 0;

    $stmt = $conn->prepare("UPDATE citizens SET
        name = COALESCE(NULLIF(?, ''), name),
        phone = COALESCE(NULLIF(?, ''), phone),
        county=?, gender=?, dob=?, headline=?, about=?, skills=COALESCE(?, skills),
        profile_completed = profile_completed OR ?
        WHERE id=?");
    $stmt->bind_param("ssssssssii", $name, $phone, $county, $gender, $dob, $headline, $about, $skills, $completeFlag, $userId);
} else {
    // "ministry" is NOT editable here — it's institutions.name, a shared
    // row other reps in the same institution also belong to. Renaming it
    // from one person's profile page would silently affect everyone else.
    // Institution naming stays an admin action.
    $name = post('name');
    $phone = post('phone');
    $position = post('position');
    $region = post('region');
    $sinceYear = post('since_year');
    $about = post('about');
    $markComplete = post('complete') === '1';
    $completeFlag = $markComplete ? 1 : 0;

    $stmt = $conn->prepare("UPDATE government_representatives SET
        name = COALESCE(NULLIF(?, ''), name),
        phone=?, position=COALESCE(NULLIF(?, ''), position), region=?, since_year=?, about=?,
        profile_completed = profile_completed OR ?
        WHERE id=?");
    $stmt->bind_param("ssssssii", $name, $phone, $position, $region, $sinceYear, $about, $completeFlag, $userId);
}

if ($stmt->execute()) {
    if (!empty($name)) {
        $_SESSION['user_name'] = $name; // keep session in sync with the edit
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
