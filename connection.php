<?php
$servername = "localhost";
$username = "u817157843_evr";
$password = "Red59854";
$dbname = "u817157843_login_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("<h1 style='color: red;'>❌ Connection failed: " . mysqli_connect_error() . "</h1>");
} else {
    die("<h1 style='color: green;'>✅ Connected successfully to the database.</h1>");
}