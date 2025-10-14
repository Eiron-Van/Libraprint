<?php
session_start();
include("../connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Login");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];

    // Sanitize inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    // Basic validation
    if ($first_name === '' || $last_name === '') {
        echo "<script>alert('First and Last name are required.'); window.history.back();</script>";
        exit();
    }

    // Validate contact number format
    if ($contact_number !== '' && !preg_match('/^09\d{9}$/', $contact_number)) {
        echo "<script>alert('Invalid contact number format. Must start with 09 and be 11 digits.'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("UPDATE user 
                            SET first_name=?, last_name=?, gender=?, address=?, birthday=?, contact_number=? 
                            WHERE user_id=?");
    $stmt->bind_param("sssssss", $first_name, $last_name, $gender, $address, $birthday, $contact_number, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
}