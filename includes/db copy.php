<?php
require_once 'config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

function query($sql) {
    global $conn;
    return mysqli_query($conn, $sql);
}

function fetch_assoc($result) {
    return mysqli_fetch_assoc($result);
}

function num_rows($result) {
    return mysqli_num_rows($result);
}

function escape_string($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}
?>