<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $otp = $_POST["otp"];

    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $users = mysqli_fetch_assoc($result);

        $db_otp = $users['reset_otp'];
        $otp_time = strtotime($users['otp_created_at']);
        $current_time = time();

        // Check OTP match and time validity (10 minutes)
        if ($otp === $db_otp && ($current_time - $otp_time) <= 600) {
            // Store email in session to use for password reset
            $_SESSION['reset_email'] = $email;
            echo "<script>alert('OTP verified! Set your new password.'); window.location.href='reset_password.html';</script>";
            exit();
        } else {
            echo "❌ Invalid or expired OTP.";
        }
    } else {
        echo "❌ Email not found.";
    }
}