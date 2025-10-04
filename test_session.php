
<?php
session_start();
include("connection.php");

echo "<h2>Session Debug Test</h2>";

echo "<h3>Session Information:</h3>";
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
        
        echo "<h3>✅ User Found!</h3>";
        echo "<p>User ID: " . htmlspecialchars($user['user_id']) . "</p>";
        echo "<p>Username: " . htmlspecialchars($user['username']) . "</p>";
        echo "<p>Full Name: " . htmlspecialchars($user['first_name'] . " " . $user['last_name']) . "</p>";
        
        echo "<h3>Test Links:</h3>";
        echo "<p><a href='User/'>Go to User Profile</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ No user found with user_id: " . $user_id . "</p>";
        
        echo "<h3>All Users in Database:</h3>";
        $all_sql = "SELECT id, user_id, username, first_name, last_name FROM users";
        $all_result = $conn->query($all_sql);
        
        if ($all_result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>DB ID</th><th>User ID</th><th>Username</th><th>Full Name</th></tr>";
            
            while ($row = $all_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ No user_id in session!</p>";
    echo "<p><a href='Login/'>Go to Login</a></p>";
}

echo "<hr>";
echo "<h3>Manual Session Test</h3>";
echo "<form method='POST'>";
echo "<input type='text' name='test_user_id' placeholder='Enter user_id to test' style='padding: 5px; margin: 5px;'>";
echo "<input type='submit' name='test_login' value='Test Login' style='padding: 5px; margin: 5px;'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
    $test_user_id = $_POST['test_user_id'];
    
    if ($test_user_id) {
        $_SESSION['user_id'] = $test_user_id;
        $_SESSION['username'] = 'test_user';
        $_SESSION['logged_in'] = true;
        
        echo "<p style='color: green;'>✅ Session set with user_id: " . htmlspecialchars($test_user_id) . "</p>";
        echo "<p><a href='test_session.php'>Refresh to see results</a></p>";
    }
}
?>
