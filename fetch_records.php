<?php
// Establish database connection
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'your_username', 'your_password');

// Fetch records
$statement = $pdo->query('SELECT * FROM students');
$students = $statement->fetchAll(PDO::FETCH_ASSOC);

// Output records as JSON
echo json_encode($students);
?>