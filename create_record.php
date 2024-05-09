<?php
// Establish database connection
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'your_username', 'your_password');

// Get data from the form
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$gender = $_POST['gender'];
$nstp = $_POST['nstp'];

// Insert new record
$statement = $pdo->prepare('INSERT INTO students (first_name, last_name, gender, nstp) VALUES (?, ?, ?, ?)');
$statement->execute([$firstName, $lastName, $gender, $nstp]);
?>
