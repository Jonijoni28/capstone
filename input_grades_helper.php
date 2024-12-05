<?php
    require_once("db_conn.php");
    require_once 'audit_logger.php';


    $conn = connect_db();

    $action = $_GET['action'];


    // In grade_management.php
if (isset($_POST['add_grade'])) {
    $student_id = $_POST['student_id'];
    $grade = $_POST['grade'];
    
    // Your existing grade addition logic here
    
    if ($grade_added) {
        logGradeActivity(
            $_SESSION['username'],
            'ADD',
            "Student ID: $student_id, Grade: $grade"
        );
    }
}
?>