<?php
// Establish database connection
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'your_username', 'your_password');

// Get the ID of the record to fetch
$school_id = $_GET['id'];

// Fetch the record
$statement = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$statement->execute([$school_id]);
$student = $statement->fetch(PDO::FETCH_ASSOC);

// Output record as JSON
echo json_encode($student);
?>
