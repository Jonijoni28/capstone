<?php
require_once 'db_conn.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
$conn = connect_db();
if (!$conn) {
    die("Database connection failed");
}

// Function to log all activities
// Function to log all activities
function logActivity($user_id, $action, $table_name, $record_id = null, $description = '') {
    global $conn;
    
    // Debug output
    error_log("Attempting to log activity: Action=$action, User=$user_id, Table=$table_name");
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id, description, ip_address, user_agent) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("issssss", $user_id, $action, $table_name, $record_id, $description, $ip_address, $user_agent);
    $stmt->execute();
 
}

// Helper functions for specific actions
function logLogin($user_id) {
    return logActivity($user_id, 'LOGIN', 'users', $user_id, 'User logged into the system');
}

function logLogout($user_id) {
    return logActivity($user_id, 'LOGOUT', 'users', $user_id, 'User logged out of the system');
}

function logCreate($user_id, $table, $record_id, $details) {
    return logActivity($user_id, 'CREATE', $table, $record_id, "Added new record: $details");
}

function logUpdate($user_id, $table, $record_id, $details) {
    return logActivity($user_id, 'UPDATE', $table, $record_id, "Modified record: $details");
}

function logDelete($user_id, $table, $record_id, $details) {
    return logActivity($user_id, 'DELETE', $table, $record_id, "Deleted record: $details");
}

// Function to get audit logs with pagination
function getAuditLogs($page = 1, $records_per_page = 10) {
    global $conn;
    
    $offset = ($page - 1) * $records_per_page;
    
    $sql = "SELECT * FROM audit_log ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ii", $records_per_page, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get total number of audit log records
function getTotalAuditLogs() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM audit_log";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Function to check if audit_log table exists, create if it doesn't
function ensureAuditTableExists() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS audit_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(50),
        table_name VARCHAR(50),
        record_id VARCHAR(50),
        description TEXT,
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        error_log("Failed to create audit_log table: " . $conn->error);
        return false;
    }
    return true;
}

// Ensure the audit_log table exists when this file is included
ensureAuditTableExists();
?>
