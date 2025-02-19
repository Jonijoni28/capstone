<?php
require_once "db_conn.php";

$sql_statement = "SELECT id, username, password, user_type FROM registration WHERE username = ?";
$sql_statement_user_info = "SELECT * FROM user_info WHERE registration_id = ?";
session_start();

// Reject all request, except POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Invalid request method.";
    exit();
}


// Check CAPTCHA first
if (!isset($_POST['captcha']) || strtoupper($_POST['captcha']) !== $_SESSION['captcha']) {
    $_SESSION['login_error'] = "Invalid CAPTCHA. Please try again.";
    header("Location: faculty");
    exit();
}

// Grab database
$db = connect_db();

// Check if 'username' and 'password' are set in POST
if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    $_SESSION['login_error'] = "Invalid username or password. Please try again.";
    header("Location: faculty");
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
    if ($input_password !== $user["password"]) {
        $_SESSION['login_error'] = "Invalid username or password. Please try again.";
        header("Location: faculty");
        exit();
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

    // Get full name for audit log
    $full_name = $user_info['first_name'] . ' ' . $user_info['last_name'];

    // Log the successful login with full name
    require_once 'audit_functions.php';
    logActivity($full_name, 'Login', 'User logged into the system');

    // Generate session identifier and set cookie
    $session_id = session_id();
    setcookie('auth', $session_id, time() + (86400 * 30), "/");

    // Redirect based on user type
    if ($user["user_type"] == "admin") {
        header('Location: homepage');
        exit();
    } elseif ($user["user_type"] == "instructor") {
        header('Location: professor');
        exit();
    }
} else {
    $_SESSION['login_error'] = "Invalid username or password. Please try again.";
    header("Location: faculty");
    exit();
}
?>


<?php
session_start();
require_once "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = connect_db();
    
    $username = $db->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // Verify the user
    $query = "SELECT r.id, r.password, r.user_type, u.first_name, u.last_name 
              FROM registration r 
              LEFT JOIN user_info u ON r.id = u.registration_id 
              WHERE r.username = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // If login is successful
        if ($password === $user['password']) {
            // Handle Remember Me functionality first before any redirects
            if ($remember) {
                // Set cookies without HttpOnly flag to allow JavaScript access
                setcookie("remembered_username", $username, time() + (86400 * 30), "/");
                setcookie("remembered_password", $password, time() + (86400 * 30), "/");
            } else {
                // Remove cookies if remember me is not checked
                setcookie("remembered_username", "", time() - 3600, "/");
                setcookie("remembered_password", "", time() - 3600, "/");
                unset($_COOKIE['remembered_username']);
                unset($_COOKIE['remembered_password']);
            }

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Set authentication cookie
            setcookie('auth', session_id(), time() + (86400 * 30), "/");

            // Redirect based on user type
            if ($user['user_type'] === 'admin') {
                header("Location: homepage");
            } else {
                header("Location: professor");
            }
            exit();
        }
    }

    // If login fails
    $_SESSION['login_error'] = "Invalid username or password";
    header("Location: faculty");
    exit();
}

// If not POST request
header("Location: faculty");
exit();
?>