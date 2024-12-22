<?php
require_once 'db_conn.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
$conn = connect_db();
if (!$conn) {
    error_log("Database connection failed in audit_functions.php");
    die("Database connection failed");
}

// Function to log activities with error handling
function logActivity($user_account, $action, $description) {
    global $conn;
    
    // Add timestamp to make sure it's being set
    $sql = "INSERT INTO audit_log (User_Account, Actions, Description, Timestamp) 
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed in logActivity: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("sss", $user_account, $action, $description);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Execute failed in logActivity: " . $stmt->error);
        return false;
    }
    
    return true;
}

// Modified function to fetch audit logs with error handling
function getAuditLogs() {
    global $conn;
    
    // Modified query to show all records
    $sql = "SELECT Audit_ID, Timestamp, Actions, Description, User_Account, 
            table_affected, record_id 
            FROM audit_log 
            ORDER BY Timestamp DESC";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed in getAuditLogs: " . $conn->error);
        return false;
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed in getAuditLogs: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        error_log("Get result failed in getAuditLogs: " . $stmt->error);
        return false;
    }
    
    return $result;
}

// Ensure the audit_log table exists with the correct structure
function ensureAuditTableExists() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS audit_log (
        Audit_ID INT PRIMARY KEY AUTO_INCREMENT,
        Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        Actions VARCHAR(50) NOT NULL,
        Description TEXT,
        User_Account VARCHAR(100) NOT NULL,
        table_affected VARCHAR(50),
        record_id INT,
        announcement_id INT,
        student_id INT,
        grade_id INT,
        user_id INT,
        INDEX idx_timestamp (Timestamp),
        FOREIGN KEY (announcement_id) REFERENCES announcement(id) ON DELETE SET NULL,
        FOREIGN KEY (student_id) REFERENCES tbl_cwts(id) ON DELETE SET NULL,
        FOREIGN KEY (grade_id) REFERENCES tbl_students_grades(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES user_info(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        error_log("Failed to create audit_log table: " . $conn->error);
        return false;
    }
    return true;
}

// Call this when the file is included
if (!ensureAuditTableExists()) {
    die("Failed to ensure audit table exists");
}

// Add these debug functions
function debugLogLogin($user_account) {
    error_log("Attempting to log login for user: " . $user_account);
    $result = logActivity($user_account, 'LOGIN', 'User logged into the system');
    error_log("Login log result: " . ($result ? "success" : "failed"));
    return $result;
}

function debugLogLogout($user_account) {
    error_log("Attempting to log logout for user: " . $user_account);
    $result = logActivity($user_account, 'LOGOUT', 'User logged out of the system');
    error_log("Logout log result: " . ($result ? "success" : "failed"));
    return $result;
}

// Replace the original functions with debug versions
function logLogin($user_account) {
    return debugLogLogin($user_account);
}

function logLogout($user_account) {
    return debugLogLogout($user_account);
}

// Add these functions to audit_functions.php
function logUserAction($userId, $action, $description, $tableAffected = 'N/A', $recordId = 'N/A') {
    $conn = connect_db();
    
    $sql = "INSERT INTO audit_log (User_Account, Actions, Description, table_affected, record_id) 
            VALUES (?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $userId, $action, $description, $tableAffected, $recordId);
    
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

