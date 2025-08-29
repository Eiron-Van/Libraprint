<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
 
$servername = "DB_HOST";
$username = "DB_USER";
$password = "DB_PASS";
$dbname = "DB_NAME";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}