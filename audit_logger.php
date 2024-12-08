<?php
// audit_logger.php

require_once 'db_conn.php';

function logAuditTrail($user_account, $action, $description, $table_affected = null, $record_id = null) {
    global $conn;
    
    if (!isset($user_account)) {
        $user_account = $_SESSION['username'] ?? 'System';
    }
    
    $sql = "INSERT INTO audit_log (
        User_Account, 
        Actions, 
        Description, 
        table_affected, 
        record_id,
        announcement_id,
        student_id,
        grade_id,
        user_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Determine which ID to set based on the affected table
    $announcement_id = null;
    $student_id = null;
    $grade_id = null;
    $user_id = null;
    
    if ($table_affected && $record_id) {
        switch ($table_affected) {
            case 'announcement':
                $announcement_id = $record_id;
                break;
            case 'tbl_cwts':
            case 'tbl_rotc':
                $student_id = $record_id;
                break;
            case 'tbl_students_grades':
                $grade_id = $record_id;
                break;
            case 'user_info':
                $user_id = $record_id;
                break;
        }
    }
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "ssssiiii", 
            $user_account, 
            $action, 
            $description, 
            $table_affected, 
            $record_id,
            $announcement_id,
            $student_id,
            $grade_id,
            $user_id
        );
        
        $success = $stmt->execute();
        if (!$success) {
            error_log("Failed to log audit trail: " . $stmt->error);
        }
        return $success;
    }
    return false;
}

// Student-related logging functions
function logStudentActivity($username, $action, $details, $student_id = null, $program = 'CWTS') {
    $table = ($program === 'CWTS') ? 'tbl_cwts' : 'tbl_rotc';
    $actions = [
        'ADD' => "Added new $program student: $details",
        'EDIT' => "Updated $program student info: $details",
        'DELETE' => "Deleted $program student: $details",
        'TRANSFER' => "Transferred $program student: $details",
        'GRADE_UPDATE' => "Updated grades for $program student: $details"
    ];
    return logAuditTrail($username, "${program}_$action", $actions[$action], $table, $student_id);
}

// Grade-related logging functions
function logGradeActivity($username, $action, $details, $grade_id = null) {
    $actions = [
        'ADD' => "Added new grade: $details",
        'EDIT' => "Updated grade: $details",
        'DELETE' => "Deleted grade: $details",
        'BULK_UPDATE' => "Bulk updated grades: $details"
    ];
    return logAuditTrail($username, "GRADE_$action", $actions[$action], 'tbl_students_grades', $grade_id);
}

// User-related logging functions
function logUserActivity($username, $action, $details, $user_id = null) {
    $actions = [
        'ADD' => "Added new user: $details",
        'EDIT' => "Updated user information: $details",
        'DELETE' => "Deleted user: $details",
        'PASSWORD_CHANGE' => "Changed password for: $details",
        'LOGIN' => "User logged in: $details",
        'LOGOUT' => "User logged out: $details",
        'LOGIN_FAILED' => "Failed login attempt: $details",
        'PROFILE_UPDATE' => "Updated profile: $details"
    ];
    return logAuditTrail($username, "USER_$action", $actions[$action], 'user_info', $user_id);
}

// Announcement-related logging functions
function logAnnouncementActivity($username, $action, $details, $announcement_id = null) {
    $actions = [
        'ADD' => "Added new announcement: $details",
        'EDIT' => "Updated announcement: $details",
        'DELETE' => "Deleted announcement: $details"
    ];
    return logAuditTrail($username, "ANNOUNCEMENT_$action", $actions[$action], 'announcement', $announcement_id);
}

// System-related logging functions
function logSystemActivity($username, $action, $details) {
    $actions = [
        'BACKUP' => "Database backup: $details",
        'RESTORE' => "Database restore: $details",
        'SETTINGS' => "System settings changed: $details",
        'MAINTENANCE' => "System maintenance: $details"
    ];
    return logAuditTrail($username, "SYSTEM_$action", $actions[$action], 'system', null);
}
?>
