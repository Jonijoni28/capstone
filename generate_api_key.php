<?php
// Database connection
$host = "localhost";
$db_name = "u641468145_nstp";
$username = "u641468145_nstp";
$password = "Nstpslsubsinfotech@2025";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Generate API Key Function
function generateApiKey() {
    return bin2hex(random_bytes(32)); // Generates a 64-character API key
}

$api_key = generateApiKey();

// Insert API Key into Database
$stmt = $conn->prepare("INSERT INTO api_keys (api_key) VALUES (?)");
$stmt->execute([$api_key]);

echo "New API Key: " . $api_key;
?>
