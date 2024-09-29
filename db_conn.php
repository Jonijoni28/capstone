<?php
    function connect_db() {
        $servername = getenv('DB_HOST') ?? "localhost";
        $username = getenv('DB_USER') ?? "root";
        $password = getenv('DB_PASS') ?? "";
        $dbname = getenv('DB_NAME') ?? "login";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
?>