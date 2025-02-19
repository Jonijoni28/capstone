<?php
    function connect_db() {
        $servername = $_ENV['DB_HOST'] ?? "localhost";
        $username = $_ENV['DB_USER'] ?? "u641468145_nstp";
        $password = $_ENV['DB_PASS'] ?? "Nstpslsubsinfotech@2025";
        $dbname = $_ENV['DB_NAME'] ?? "u641468145_nstp";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }


    