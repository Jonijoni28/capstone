<?php
require_once("db_conn.php");
session_start(); // Add session start
$conn = connect_db();

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
$new_instructor = $data['instructor'];
$student_ids = $data['studentIds'];

// Begin transaction
$conn->begin_transaction();

try {
    // Create a prepared statement with placeholders for each student ID
    $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
    
    // First, get the current instructor for logging purposes
    $sql_get_instructor = "SELECT instructor FROM tbl_cwts WHERE school_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql_get_instructor);
    $stmt->bind_param("s", $student_ids[0]);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $old_instructor = $row['instructor'] ?? 'None';
    
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
        // Log the transfer activity
        $username = $_SESSION['username'] ?? 'System';
        $action = "From: $old_instructor To: $new_instructor, Students: " . implode(', ', $student_ids);
        
        
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