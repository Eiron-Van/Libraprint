<?php

// send_otp.php
require '../vendor/autoload.php';

use SendGrid\Mail\Mail;

include("../connection.php"); // your DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    
    // Check if email exists
    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $users = mysqli_fetch_assoc($result);
        $otp = rand(100000, 999999); // 6-digit OTP
        $created_at = date("Y-m-d H:i:s");

        // Save OTP in DB
        $update = "UPDATE users SET reset_otp='$otp', otp_created_at='$created_at' WHERE email='$email'";
        mysqli_query($conn, $update);

        // Send email via SendGrid
        $email_to_send = new Mail();
        $email_to_send->setFrom("20220321@cstc.edu.ph", "LibraPrint");
        $email_to_send->setSubject("Your OTP Code for Password Reset");
        $email_to_send->addTo($email, $users['first_name']);
        $email_to_send->addContent("text/plain", "Your OTP is: $otp");

        require_once __DIR__ . '/../vendor/autoload.php';

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $sendgrid = new \SendGrid($_ENV['SENDGRID_API_KEY']);

        try {
            $response = $sendgrid->send($email_to_send);
            if ($response->statusCode() == 202) {
                echo "<script>alert('OTP sent to your email!'); window.location.href='verify_otp.html';</script>";
            } else {
                echo "Failed to send email.";
            }
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage();
        }
    } else {
        echo "Email not found.";
    }
}
