<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "research_portal"
);

if(!$conn){
    die("Database connection failed: " . mysqli_connect_error());
}

?>
