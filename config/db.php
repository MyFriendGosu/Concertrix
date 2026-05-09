<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "concert_ticketing";

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset for security (prevents encoding issues)
$conn->set_charset("utf8mb4");