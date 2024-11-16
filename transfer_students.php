<?php
require_once("db_conn.php");
$conn = connect_db();

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
$instructor = $data['instructor'];
$studentIds = $data['studentIds'];

// Begin transaction
$conn->begin_transaction();

try {
    // Create a prepared statement with placeholders for each student ID
    $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';
    
    // Update the instructor field and mark as transferred
    $sql = "UPDATE tbl_cwts 
            SET instructor = ?, 
                transferred = 1 
            WHERE school_id IN ($placeholders)";
    
    // Create array of parameters starting with instructor
    $params = array_merge([$instructor], $studentIds);
    
    // Create type string for bind_param (s for instructor, s for each student ID)
    $types = str_repeat('s', count($params));
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update students: " . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows > 0) {
        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("No students were updated. Please check the student IDs.");
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>