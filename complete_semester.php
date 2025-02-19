<?php
require_once("db_conn.php");
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $semester = $data['semester'] ?? '';
    $instructor = $_SESSION['username'];

    $conn = connect_db();

    // Check if all students have grades and status
    $sql = "SELECT COUNT(*) as total, 
            SUM(CASE WHEN g.status IS NOT NULL AND g.status != '' THEN 1 ELSE 0 END) as with_status
            FROM tbl_cwts c
            LEFT JOIN tbl_students_grades g ON c.school_id = g.school_id AND g.semester = ?
            WHERE c.instructor = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $semester, $instructor);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data['total'] != $data['with_status']) {
        throw new Exception('Not all students have grades and status');
    }

    // Mark semester as completed
    $sql = "INSERT INTO semester_completion (instructor, semester, is_completed) 
            VALUES (?, ?, 1) 
            ON DUPLICATE KEY UPDATE is_completed = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $instructor, $semester);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update semester completion status');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>