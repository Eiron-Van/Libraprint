<?php
$servername = "u817157843_login_db";
$username = "evr";
$password = "Red59854";
$dbname = "users";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}