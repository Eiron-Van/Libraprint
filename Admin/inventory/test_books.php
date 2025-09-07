<?php
require __DIR__ . '/vendor/autoload.php'; // Load dotenv

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$username   = $_ENV['DB_USER'];
$password   = $_ENV['DB_PASS'];
$dbname     = $_ENV['DB_NAME'];

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query the table
$sql = "SELECT * FROM book_inventory";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Check if rows exist
if ($result->num_rows > 0) {
    echo "<h2>Books Found: " . $result->num_rows . "</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>";
    // Dynamically print headers
    $fields = $result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>{$field->name}</th>";
    }
    echo "</tr>";

    // Print rows
    $result->data_seek(0); // Reset pointer
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No books found.";
}

$conn->close();
?>
