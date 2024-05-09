<?php
// Establish database connection
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'your_username', 'your_password');

// Get data from the form
$school_id = $_POST['studentId'];
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$gender = $_POST['gender'];
$nstp = $_POST['nstp'];

// Update the record
$statement = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, gender = ?, nstp = ? WHERE id = ?');
$statement->execute([$firstName, $lastName, $gender, $nstp, $school_id]);
?>
