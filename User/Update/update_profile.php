<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../../connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Login");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];

    // Sanitize inputs
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    // Validate contact number format
    if ($contact_number !== '' && !preg_match('/^09\d{9}$/', $contact_number)) {
        echo "<script>alert('Invalid contact number format. Must start with 09 and be 11 digits.'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("UPDATE users 
                            SET email=?, contact_number=? 
                            WHERE user_id=?");
    $stmt->bind_param("sss", $email, $contact_number, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='https://libraprintlucena.com/User';</script>";
    } else {
        echo "<script>alert('Error updating profile. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
}