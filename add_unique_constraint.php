<?php
include("connection.php");

echo "<h2>Add Unique Constraint to User ID</h2>";

// Check if unique constraint already exists
$check_sql = "SHOW INDEX FROM users WHERE Key_name = 'unique_user_id'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Unique constraint already exists on user_id column.</p>";
} else {
    echo "<p style='color: blue;'>ℹ️ No unique constraint found. Adding one...</p>";
    
    try {
        // Add unique constraint to user_id column
        $alter_sql = "ALTER TABLE users ADD CONSTRAINT unique_user_id UNIQUE (user_id)";
        
        if ($conn->query($alter_sql) === TRUE) {
            echo "<p style='color: green;'>✅ Successfully added unique constraint to user_id column!</p>";
            echo "<p>This will prevent future duplicate user_ids from being inserted.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding unique constraint: " . $conn->error . "</p>";
            echo "<p>This might be because there are still duplicate user_ids in the database.</p>";
            echo "<p>Please run the fix_duplicate_userids.php script first to remove duplicates.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<h3>Database Table Structure</h3>";

// Show the current table structure
$structure_sql = "DESCRIBE users";
$structure_result = $conn->query($structure_sql);

if ($structure_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<h3>Indexes on Users Table</h3>";

// Show all indexes on the users table
$index_sql = "SHOW INDEX FROM users";
$index_result = $conn->query($index_sql);

if ($index_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Table</th><th>Non_unique</th><th>Key_name</th><th>Seq_in_index</th><th>Column_name</th><th>Index_type</th></tr>";
    
    while ($row = $index_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Table'] . "</td>";
        echo "<td>" . $row['Non_unique'] . "</td>";
        echo "<td>" . $row['Key_name'] . "</td>";
        echo "<td>" . $row['Seq_in_index'] . "</td>";
        echo "<td>" . $row['Column_name'] . "</td>";
        echo "<td>" . $row['Index_type'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
