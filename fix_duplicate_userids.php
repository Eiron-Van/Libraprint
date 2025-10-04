<?php
include("connection.php");

echo "<h2>User ID Duplicate Checker</h2>";

// Check for duplicate user_ids
$sql = "SELECT user_id, COUNT(*) as count FROM users GROUP BY user_id HAVING COUNT(*) > 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h3 style='color: red;'>❌ Found Duplicate User IDs:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>User ID</th><th>Count</th><th>Users with this ID</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        
        // Get all users with this duplicate user_id
        $duplicate_sql = "SELECT id, username, first_name, last_name, email FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($duplicate_sql);
        $stmt->bind_param("s", $row['user_id']);
        $stmt->execute();
        $duplicate_result = $stmt->get_result();
        
        echo "<td>";
        while ($user = $duplicate_result->fetch_assoc()) {
            echo "ID: " . $user['id'] . " - " . $user['username'] . " (" . $user['first_name'] . " " . $user['last_name'] . ") - " . $user['email'] . "<br>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<h3 style='color: green;'>✅ No duplicate user_ids found!</h3>";
}

echo "<hr>";

// Show all user_ids for reference
echo "<h3>All User IDs in Database:</h3>";
$all_sql = "SELECT id, user_id, username, first_name, last_name FROM users ORDER BY user_id";
$all_result = $conn->query($all_sql);

if ($all_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
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

echo "<hr>";
echo "<h3>Fix Duplicate User IDs</h3>";
echo "<p>Click the button below to generate unique user_ids for all users:</p>";
echo "<form method='POST' action=''>";
echo "<input type='submit' name='fix_duplicates' value='Generate Unique User IDs' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_duplicates'])) {
    echo "<h3>Fixing Duplicate User IDs...</h3>";
    
    // Get all users
    $users_sql = "SELECT id, username, first_name, last_name FROM users";
    $users_result = $conn->query($users_sql);
    
    $updated_count = 0;
    
    while ($user = $users_result->fetch_assoc()) {
        // Generate a unique user_id
        $new_user_id = generateUniqueUserID($conn);
        
        // Update the user
        $update_sql = "UPDATE users SET user_id = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_user_id, $user['id']);
        
        if ($stmt->execute()) {
            echo "✅ Updated user " . $user['username'] . " (" . $user['first_name'] . " " . $user['last_name'] . ") with new user_id: " . $new_user_id . "<br>";
            $updated_count++;
        } else {
            echo "❌ Failed to update user " . $user['username'] . "<br>";
        }
    }
    
    echo "<h3 style='color: green;'>✅ Successfully updated " . $updated_count . " users with unique user_ids!</h3>";
    echo "<p><a href='fix_duplicate_userids.php'>Refresh to see updated results</a></p>";
}

function generateUniqueUserID($conn) {
    do {
        // Generate a random 8-character alphanumeric user_id
        $user_id = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        
        // Check if this user_id already exists
        $check_sql = "SELECT COUNT(*) as count FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
    } while ($count > 0); // Keep generating until we find a unique one
    
    return $user_id;
}
?>
