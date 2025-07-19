<?php
session_start();
include("../connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['reset_email'])) {
        echo "❌ Session expired. Start over.";
        exit();
    }

    $email = $_SESSION['reset_email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "❌ Passwords do not match.";
        exit();
    }

    // Securely hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update the password in the database
    $query = "UPDATE users SET password = '$hashed_password', reset_otp = NULL, otp_created_at = NULL WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        unset($_SESSION['reset_email']);  // Clean up session
        echo "<script>alert('✅ Password reset successful! Please log in.'); window.location.href='login.html';</script>";
    } else {
        echo "❌ Failed to update password.";
    }
}