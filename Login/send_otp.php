<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../mailer.php'; // ✅ use your centralized mailer
include("../connection.php"); // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $users = $result->fetch_assoc();
        $otp = rand(100000, 999999); // 6-digit OTP
        $created_at = date("Y-m-d H:i:s");

        // Save OTP in DB
        $update = $conn->prepare("UPDATE users SET reset_otp = ?, otp_created_at = ? WHERE email = ?");
        $update->bind_param("sss", $otp, $created_at, $email);
        $update->execute();

        // Build email
        $subject = "Your OTP Code for Password Reset";
        $bodyHtml = "
            Hello <b>" . htmlspecialchars($users['first_name']) . "</b>,<br><br>
            Your OTP is: <b>$otp</b><br>
            This code is valid for 10 minutes.<br><br>
            If you did not request this, please ignore this email.";

        // Send email using Gmail SMTP via mailer.php
        $result = sendEmail($email, $users['first_name'], $subject, $bodyHtml);

        if ($result['success']) {
            echo "<script>alert('OTP sent to your email!'); window.location.href='verify_otp.html';</script>";
        } else {
            echo "Failed to send email: " . $result['error'];
        }

    } else {
        echo "Email not found.";
    }
}
