<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("db_conn.php");
require_once("audit_functions.php");
$conn = connect_db();

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
$new_instructor = $data['instructor'];
$student_ids = $data['studentIds'];

// Begin transaction
$conn->begin_transaction();

try {
    // Get the user's full name
    $user_id = $_SESSION['user_id'];
    $query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $full_name = $user['full_name'];

    // Create a prepared statement with placeholders for each student ID
    $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
    
    // First, get the details of all students being transferred
    $sql_get_students = "SELECT school_id, first_name, last_name, nstp, course, instructor 
                        FROM tbl_cwts 
                        WHERE school_id IN ($placeholders)";
    
    $stmt = $conn->prepare($sql_get_students);
    $stmt->bind_param(str_repeat('s', count($student_ids)), ...$student_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get the old instructor for the first student (assuming all selected students have the same instructor)
    $old_instructor = $students[0]['instructor'] ?? 'None';
    
    // Update the instructor field and mark as transferred
    $sql = "UPDATE tbl_cwts 
            SET instructor = ?, 
                transferred = 1 
            WHERE school_id IN ($placeholders)";
    
    // Create array of parameters starting with instructor
    $params = array_merge([$new_instructor], $student_ids);
    
    // Create type string for bind_param (s for instructor, s for each student ID)
    $types = str_repeat('s', count($params));
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update students: " . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows > 0) {
        // Create detailed description for logging
        $student_details = array();
        foreach ($students as $student) {
            $student_details[] = "{$student['first_name']} {$student['last_name']} " .
                               "(ID: {$student['school_id']}, NSTP: {$student['nstp']}, " .
                               "Program: {$student['course']})";
        }
        
        $description = "Transferred students to instructor '$new_instructor':\n" . 
                      implode("\n", $student_details);

        // Log the transfer activity with full name
        $result = logActivity(
            $full_name,  // Using full name instead of username
            'Transfer Students',
            $description,
            'CWTS Students Table',
            null
        );
        
        if (!$result) {
            throw new Exception("Failed to log activity");
        }
        
        // Commit transaction
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Students transferred successfully',
            'affected_rows' => $stmt->affected_rows
        ]);
    } else {
        throw new Exception("No students were updated. Please check the student IDs.");
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>