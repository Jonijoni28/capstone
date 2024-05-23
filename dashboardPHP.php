<?php
require_once ("db_conn.php");

$conn = connect_db();

$all_student_count_statement = 
    "SELECT COUNT(*) FROM (SELECT `school_id` from `tbl_cwts`) as id";
$all_rotc_count_statement = 
    "SELECT COUNT(*) FROM 
    (SELECT `school_id` from `tbl_cwts` WHERE `nstp` = \"ROTC\") as id";
$all_cwts_count_statement = "SELECT COUNT(*) FROM 
    (SELECT `school_id` from `tbl_cwts` WHERE `nstp` = \"CWTS\") as id";

global $conn, $all_student_count_statement, $all_rotc_count_statement, 
    $all_cwts_count_statement;

function get_sql_query_result(string $query): bool | mysqli_result {
    global $conn;

    $result = $conn->query($query);

    if ($conn->error) {
        error_log("SQL Error: ".$conn->error);
        return false;
    }

    return $result;
}

function get_all_student_count(): int | string {
    global $all_student_count_statement;

    $result = get_sql_query_result($all_student_count_statement);

    $row = $result->fetch_assoc();

    $result->free_result();

    return intval($row['COUNT(*)']);
}

function get_rotc_student_count(): int | string {
    global $all_rotc_count_statement;

    $result = get_sql_query_result($all_rotc_count_statement);

    $row = $result->fetch_assoc();

    $result->free_result();

    return intval($row['COUNT(*)']);
}

function get_cwts_student_count(): int | string {
    global $all_cwts_count_statement;
    
    $result = get_sql_query_result($all_cwts_count_statement);

    $row = $result->fetch_assoc();

    $result->free_result();

    return intval($row['COUNT(*)']);
}