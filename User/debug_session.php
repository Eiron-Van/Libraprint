<?php
session_start();
include("../connection.php");

echo "<h2>Session Debug Information</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    echo "<h3>User Data from Database:</h3>";
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Test initials generation
        $first_initial = strtoupper(substr($user['first_name'], 0, 1));
        $last_initial = strtoupper(substr($user['last_name'], 0, 1));
        $initials = $first_initial . $last_initial;
        
        echo "<h3>Generated Initials: " . $initials . "</h3>";
    } else {
        echo "<p style='color: red;'>No user found with user_id: " . $user_id . "</p>";
    }
} else {
    echo "<p style='color: red;'>No user_id in session!</p>";
}
?>
