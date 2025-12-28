<?php

$conn = mysqli_connect(
	"localhost",
	"studentuser",
	"student123",
	"research_portal"
);

if(!$conn){
	die("Database connection failed");
}

?>
