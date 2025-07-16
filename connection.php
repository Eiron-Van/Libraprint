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
}
$query = "SELECT username FROM users LIMIT 1";
$result = mysqli_query($conn, $query);

// Check if we got a result
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $username = $row['username'];
    die("<h1 style='color:green;'>✅ Connected successfully! First username in users table is: <u>$username</u></h1>");
} else {
    die("<h1 style='color:orange;'>✅ Connected, but no users found in the table.</h1>");
}
?>