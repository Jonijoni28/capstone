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

// Function to log activities
function logActivity($user_account, $action, $description) {
    global $conn;
    
    $sql = "INSERT INTO audit_log (User_Account, Actions, Description) 
            VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("sss", $user_account, $action, $description);
    return $stmt->execute();
}

// Helper functions for specific actions
function logLogin($user_account) {
    return logActivity($user_account, 'LOGIN', 'User logged into the system');
}

function logLogout($user_account) {
    return logActivity($user_account, 'LOGOUT', 'User logged out of the system');
}

function logCreate($user_account, $details) {
    return logActivity($user_account, 'CREATE', "Added new record: $details");
}

function logUpdate($user_account, $details) {
    return logActivity($user_account, 'UPDATE', "Modified record: $details");
}

function logDelete($user_account, $details) {
    return logActivity($user_account, 'DELETE', "Deleted record: $details");
}

// In audit_functions.php
function getAuditLogs($page = 1, $records_per_page = 10) {
    global $conn;
    
    $offset = ($page - 1) * $records_per_page;
    
    // Update to use 'Timestamp' instead of 'created_at'
    $sql = "SELECT * FROM audit_log ORDER BY Timestamp DESC LIMIT ? OFFSET ?";
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

function ensureAuditTableExists() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS audit_log (
        Audit_ID INT PRIMARY KEY AUTO_INCREMENT,
        Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        Actions VARCHAR(50),
        Description TEXT,
        User_Account VARCHAR(100),
        table_affected VARCHAR(50),
        record_id INT,
        announcement_id INT,
        student_id INT,
        grade_id INT,
        user_id INT,
        FOREIGN KEY (announcement_id) REFERENCES announcement(id) ON DELETE SET NULL,
        FOREIGN KEY (student_id) REFERENCES tbl_cwts(id) ON DELETE SET NULL,
        FOREIGN KEY (grade_id) REFERENCES tbl_students_grades(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES user_info(id) ON DELETE SET NULL
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
