<?php
require_once "db_conn.php";

$db = connect_db();

$sql = "ALTER TABLE user_info 
        ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL,
        ADD COLUMN reset_expiry DATETIME DEFAULT NULL";

try {
    if ($db->query($sql) === TRUE) {
        echo "Reset columns added successfully";
    } else {
        echo "Error adding reset columns: " . $db->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$db->close();
?> 