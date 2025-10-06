<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include  '../connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$host = "localhost";   // or your database host
$user = "root";        // your DB username
$pass = "";            // your DB password
$db   = "libraprint";  // your DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// query
if (!empty($search)) {
    $safe_search = $conn->real_escape_string($search);
    $sql = "SELECT * FROM book_inventory 
            WHERE author LIKE '%$safe_search%' 
            OR title LIKE '%$safe_search%' 
            OR accession_no LIKE '%$safe_search%' 
            OR class_no LIKE '%$safe_search%' 
            OR status LIKE '%$safe_search%'";
} else {
    $sql = "SELECT * FROM book_inventory";
}
$result = $conn->query($sql);
if (!$result) {
    die("Invalid query: " . $conn->error);
}
$num_rows = $result->num_rows;

// output
echo "<p><strong>$num_rows</strong> results for '" . htmlspecialchars($search) . "'</p>";

echo "<table class='w-full border'>";
echo "<thead class='bg-gray-200'>
        <tr>
          <th class='p-2'>Author</th>
          <th class='p-2'>Title</th>
          <th class='p-2 text-center'>Status</th>
          <th class='p-2 text-center'>Action</th>
        </tr>
      </thead>
      <tbody>";

while ($row = $result->fetch_assoc()) {
    $statusClass = strtolower($row['status']) === 'available' ? 'text-green-600' : 'text-red-600';

    echo "<tr>
            <td class='p-2'>" . htmlspecialchars($row['author']) . "</td>
            <td class='p-2'>" . htmlspecialchars($row['title']) . "</td>
            <td class='p-2 font-semibold text-center $statusClass'>" . htmlspecialchars($row['status']) . "</td>
            <td class='p-2 text-center'>";
    
    if (strtolower($row['status']) === 'available') {
        echo "<a href='reserve_book.php?item_id=" . urlencode($row['item_id']) . "' 
                class='bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600'>
                Reserve
              </a>";
    } else {
        echo "<span class='text-gray-400'>Not Available</span>";
    }

    echo "</td></tr>";
}

echo "</tbody></table>";
?>
