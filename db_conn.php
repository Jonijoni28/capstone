<?php
    function connect_db() {
        $servername = $_ENV['DB_HOST'] ?? "localhost";
        $username = $_ENV['DB_USER'] ?? "root";
        $password = $_ENV['DB_PASS'] ?? "";
        $dbname = $_ENV['DB_NAME'] ?? "login";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }


    