<?php
session_start();
include("config.php"); // <-- your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check user
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $username;
        header("Location: https://libraprintlucena.com"); // <-- redirect to a page after login
    } else {
        echo "<script>alert('Invalid Username or Password'); window.history.back();</script>";
    }
}
?>