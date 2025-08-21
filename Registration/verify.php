<?php
include("../connection.php");

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE verification_token = ? AND is_verified = 0 LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE user_id = ?");
        $update->bind_param("s", $user_id);
        $update->execute();

        echo "✅ Your email has been verified! You can now <a href='/Login'>login</a>.";
    } else {
        echo "❌ Invalid or expired verification link.";
    }
}
?>