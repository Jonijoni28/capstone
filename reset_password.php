<?php
session_start();
require_once "db_conn.php";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $db = connect_db();
    
    // Check if token exists and is not expired
    $stmt = $db->prepare("SELECT * FROM user_info WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['reset_message'] = "Error: Invalid or expired reset link";
        header("Location: forgot_password");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Add your CSS here (similar to forgot_password.php) -->
</head>
<body>
    <div class="container">
        <div class="reset-box">
            <h2>Reset Password</h2>
            <form action="update_password" method="post">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <div class="form-group">
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html> 