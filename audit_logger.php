<?php
// audit_logger.php


function logAuditTrail($user_account, $action, $description, $table_affected = null, $record_id = null) {
    global $conn;
    
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
    
    if ($table_affected) {
        switch ($table_affected) {
            case 'announcement':
                $announcement_id = $record_id;
                break;
            case 'tbl_cwts':
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
        return $stmt->execute();
    }
    return false;
}

// Update other logging functions to include table and record information
function logAnnouncementActivity($username, $action, $details = '', $announcement_id = null) {
    $actions = [
        'ADD' => "Added new announcement: $details",
        'EDIT' => "Updated announcement: $details",
        'DELETE' => "Deleted announcement: $details"
    ];
    return logAuditTrail($username, "ANNOUNCEMENT_$action", $actions[$action], 'announcement', $announcement_id);
}

function logCWTSActivity($username, $action, $details, $student_id = null) {
    $actions = [
        'ADD' => "Added new CWTS student: $details",
        'EDIT' => "Updated CWTS student info: $details",
        'DELETE' => "Deleted CWTS student: $details",
        'TRANSFER' => "Transferred CWTS student: $details"
    ];
    return logAuditTrail($username, "CWTS_$action", $actions[$action], 'tbl_cwts', $student_id);
}

function logGradeActivity($username, $action, $details, $grade_id = null) {
    $actions = [
        'ADD' => "Added new grade: $details",
        'EDIT' => "Updated grade: $details",
        'DELETE' => "Deleted grade: $details"
    ];
    return logAuditTrail($username, "GRADE_$action", $actions[$action], 'tbl_students_grades', $grade_id);
}

function logUserActivity($username, $action, $details, $user_id = null) {
    $actions = [
        'ADD' => "Added new user: $details",
        'EDIT' => "Updated user information: $details",
        'DELETE' => "Deleted user: $details",
        'PASSWORD_CHANGE' => "Changed password for: $details"
    ];
    return logAuditTrail($username, "USER_$action", $actions[$action], 'user_info', $user_id);
}
?>
