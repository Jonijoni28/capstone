<?php
// Establish database connection
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'your_username', 'your_password');

// Get the ID of the record to delete
$data = json_decode(file_get_contents("php://input"), true);
$school_id = $data['id'];

// Delete the record
$statement = $pdo->prepare('DELETE FROM students WHERE id = ?');
$statement->execute([$school_id]);
?>
