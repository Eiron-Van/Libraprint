<?php
file_put_contents("debug_log.txt", print_r($_POST, true), FILE_APPEND);

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_id($_POST['session'] ?? '');
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['pending_registration'])) {
    echo json_encode(["status" => "error", "message" => "No registration data found."]);
    exit();
}
if (!isset($_POST['fingerprint_data'])) {
    echo json_encode(["status" => "error", "message" => "No fingerprint data received."]);
    exit;
}

include "../connection.php";
include "../function.php";
require '../vendor/autoload.php';
use SendGrid\Mail\Mail;

$data = $_SESSION['pending_registration'];



$user_id = random_num(20);
$token = bin2hex(random_bytes(32));
$address = "[".$data['barangay'].", ".$data['city']."]";

$stmt = $conn->prepare("INSERT INTO users 
    (user_id, username, first_name, last_name, gender, address, birthday, contact_number, email, password, fingerprint, verification_token, is_verified) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

if (!$stmt) {
    error_log("SQL error: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database prepare failed."]);
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
    // Send verification email
    $verifyLink = "https://libraprintlucena.com/Registration/verify.php?token=" . $token;

    $emailObj = new Mail();
    $emailObj->setFrom("20220321@cstc.edu.ph", "Libraprint");
    $emailObj->setSubject("Verify your email address");
    $emailObj->addTo($data['email'], $data['firstname'] . " " . $data['lastname']);
    $emailObj->addContent("text/html",
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

        if ($response->statusCode() >= 400) {
            error_log("SendGrid error: " . $response->statusCode() . " " . $response->body());
            echo json_encode(["status" => "error", "message" => "Email sending failed: ".$e->getMessage()]);
        } else {
            unset($_SESSION['pending_registration']); // clear session
            echo json_encode(["status" => "success", "message" => "Registration successful! Please verify your email."]);
        }
        
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Email sending failed: ".$e->getMessage()]);
    }
} else {
    error_log("DB insert error: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database insert failed."]);
}

$stmt->close();
$conn->close();
