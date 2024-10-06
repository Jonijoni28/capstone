<?php
require_once "db_conn.php";
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
