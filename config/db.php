<?php

$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'studentuser';
$pass = getenv('DB_PASSWORD') ?: 'student123';
$db   = getenv('DB_NAME') ?: 'research_portal';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>
