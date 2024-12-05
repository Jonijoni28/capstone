<?php
require_once "db_conn.php";
require_once 'audit_logger.php';

$sql_statement = "SELECT id, username, password, user_type FROM registration WHERE username = ?";
$sql_statement_user_info = "SELECT * FROM user_info WHERE registration_id = ?";
session_start();

// Reject all request, except POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Invalid request method.";
}

// Grab database
$db = connect_db();

// Check if 'username' and 'password' are set in POST
if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    http_response_code(400);
    header("Location: faculty.php");
    exit();
}

// Grab username + password
$input_username = $_POST["username"];
$input_password = $_POST["password"];

// Check first if user exists
$stmt = $db->prepare($sql_statement);
$stmt->bind_param("s", $input_username);
$stmt->execute();
$stmt_results = $stmt->get_result();

if ($stmt_results->num_rows > 0) {
    $user = $stmt_results->fetch_assoc();

    // Verify the password
    if (!$input_password == $user["password"]) {
        http_response_code(401);
        echo "Invalid username or password.";
    }

    $stmt_user_info = $db->prepare($sql_statement_user_info);
    $stmt_user_info->bind_param("i", $user["id"]);
    $stmt_user_info->execute();
    $stmt_user_info_results = $stmt_user_info->get_result();
    $user_info = $stmt_user_info_results->fetch_assoc();

    // Set the session variable
    $_SESSION['user_id'] = $user_info["id"];
    $_SESSION['username'] = $user["username"];
    $_SESSION['user_type'] = $user["user_type"];

    if ($user["user_type"] == "admin") {
        http_response_code(301);
        http_response_code(301);
        // Generate a session identifier (or token)
        $session_id = session_id();

        // Set a cookie for the session
        setcookie('auth', $session_id, time() + (86400 * 30), "/"); // 30 days expiry

        header('Location: homepage.php');
        exit();
    }

    if ($user["user_type"] == "instructor") {
        http_response_code(301);
        // Generate a session identifier (or token)
        $session_id = session_id();

        // Set a cookie for the session
        setcookie('auth', $session_id, time() + (86400 * 30), "/"); // 30 days expiry

        header('Location: professor.php');
        exit();
    }
} else {
    http_response_code(401);
    echo "Invalid username or password.";
}



require_once 'db_conn.php';
session_start();

// Keep all the function definitions but remove the direct calls at the bottom

function logActivity($user_id, $action, $table_name, $record_id = null, $description = '') {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id, description, ip_address, user_agent) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("issssss", $user_id, $action, $table_name, $record_id, $description, $ip_address, $user_agent);
    
    $success = $stmt->execute();
    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    return $success;
}

require_once 'db_conn.php';
require_once 'audit_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Your existing login verification
    if ($login_successful) {
        $_SESSION['user_id'] = $user_id;
        // Log the login
        logLogin($user_id);
        // Rest of your login code
    }
}
?>

<?php

// In login.php
if ($login_successful) {
    logUserAuth($username, 'LOGIN');
}

// In logout.php
logUserAuth($_SESSION['username'], 'LOGOUT');

// In announcement management
// When adding announcement
logAnnouncementActivity($_SESSION['username'], 'ADD', $announcement_title);

// When updating announcement
logAnnouncementActivity($_SESSION['username'], 'EDIT', $announcement_title);

// When deleting announcement
logAnnouncementActivity($_SESSION['username'], 'DELETE', $announcement_title);

// In grade management
// When adding grades
logGradeActivity($_SESSION['username'], 'ADD', "Student ID: $student_id, Grade: $grade");

// When updating grades
logGradeActivity($_SESSION['username'], 'EDIT', "Student ID: $student_id, New Grade: $new_grade");

// When transferring students
logTransferActivity($_SESSION['username'], "From: $from_instructor To: $to_instructor, Students: $student_ids");
?>