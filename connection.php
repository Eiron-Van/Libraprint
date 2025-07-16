<?php
$servername = "localhost";
$username = "u817157843_evr";
$password = "Red59854";
$dbname = "u817157843_login_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}