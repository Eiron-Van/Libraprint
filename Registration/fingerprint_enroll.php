<?php
if (isset($_GET['session'])) {
    session_id($_GET['session']);  // Force PHP to resume that session
}
session_start();
if (!isset($_SESSION['pending_registration'])) {
    echo "No registration data found.";
    exit();
}

require '../vendor/autoload.php';
use SendGrid\Mail\Mail;

// Example: once fingerprint is captured and returned
if (isset($_POST['fingerprint_data'])) {
    $data = $_SESSION['pending_registration'];
    
    include "../connection.php";


    $user_id = random_num(20);
    $token = bin2hex(random_bytes(32));
    $address = "[".$data['barangay'].", ".$data['city']."]";
    
    $stmt = $conn->prepare("INSERT INTO users 
        (user_id, username, first_name, last_name, gender, address, birthday, contact_number, email, password, fingerprint, verification_token, is_verified) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

        // Check if the statement was prepared successfully
        if (!$stmt) {
            error_log("SQL error: " . $conn->error);
            echo "An error occurred. Please try again later.";
            exit();
        }

    $stmt->bind_param("ssssssssssss",
        $user_id,
        $data['username'],
        $data['firstname'],
        $data['lastname'],
        $data['gender'],
        $address,
        $data['birthdate'],
        $data['contactnumber'],
        $data['email'],
        $data['password'],
        $_POST['fingerprint_data'],
        $token
    );

    if ($stmt->execute()) {

        // ✅ Send verification email
        $verifyLink = "https://libraprintlucena.com/Registration/verify.php?token=" . $token;

        $emailObj = new Mail();
        $emailObj->setFrom("20220321@cstc.edu.ph", "Libraprint");
        $emailObj->setSubject("Verify your email address");
        $emailObj->addTo($data['email'], $data['firstname'] . " " . $data['lastname']);
        $emailObj->addContent(
            "text/html",
            "Hello <b>" . htmlspecialchars($data['firstname']) . "</b>,<br><br>
            Please verify your email by clicking the link below:<br>
            <a href='$verifyLink'>$verifyLink</a><br><br>
            Thank you!"
        );

        require_once __DIR__ . '/../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
        $dotenv->load();

        $sendgrid = new \SendGrid($_ENV['SENDGRID_API_KEY']);

        try {
            $response = $sendgrid->send($emailObj);
            unset($_SESSION['pending_registration']); // clear session
            echo "<script>alert('Registration successful! Please check your email to verify your account.'); window.location.href='/Login';</script>";
        } catch (Exception $e) {
            echo 'Email sending failed: '. $e->getMessage() ."\n";
        }
    } else {
        error_log("DB insert error: " . $stmt->error);
        echo "An error occured while saving user. Please try again.";
    }
    $stmt->close();
    $conn->close();
}
