<?php
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
require_once __DIR__ . '/../mailer.php';

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

    $result = sendEmail(
        $data['email'],
        $data['firstname'] . " " . $data['lastname'],
        "Verify your email address",
        "Hello <b>" . htmlspecialchars($data['firstname']) . "</b>,<br><br>
        Please verify your email by clicking the link below:<br>
        <a href='$verifyLink'>$verifyLink</a><br><br>
        Thank you!"
    );

    if ($result['status'] === "success") {
        unset($_SESSION['pending_registration']);
        echo json_encode(["status" => "success", "message" => "Registration successful! Please check your email."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email sending failed: " . $result['message']]);
    }
    } else {
        error_log("DB insert error: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Database insert failed."]);
    }

$stmt->close();
$conn->close();



