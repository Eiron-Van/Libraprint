<?php
require '../../connection.php';

$type = $_GET['type'] ?? 'Login_record';
$search = trim($_GET['search'] ?? '');

$valid_tables = ['Login_record', 'Book_record', 'Reservation', 'Claim_log', 'Borrow_log'];

if (!in_array($type, $valid_tables)) {
    echo "<p class='text-red-400'>Invalid log type.</p>";
    exit;
}

// Base query
$query = "SELECT * FROM $type";
$params = [];

// Apply search
if ($search !== '') {
    $query .= " WHERE ";
    switch ($type) {
        case 'Login_record':
            $query .= "(user_id LIKE ? OR purpose LIKE ? OR location LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
            break;
        case 'Book_record':
            $query .= "(user_id LIKE ? OR book_id LIKE ? OR read_date LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
            break;
        case 'Reservation':
            $query .= "(user_id LIKE ? OR item_id LIKE ? OR purpose LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
            break;
        case 'Claim_log':
            $query .= "(user_id LIKE ? OR item_id LIKE ? OR purpose LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
            break;
        case 'Borrow_log':
            $query .= "(user_id LIKE ? OR book_id LIKE ? OR status LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
            break;
    }
}

$query .= " ORDER BY id DESC LIMIT 100";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo "<p class='text-red-400'>Query preparation failed: " . $conn->error . "</p>";
    exit;
}


$result = [];
$stmt->execute();
$res = $stmt->get_result(); // Get the result set

while ($row = $res->fetch_assoc()) {
    $result[] = $row;
}


if (!$result) {
    echo "<p class='text-gray-400'>No records found.</p>";
    exit;
}

// Display Table
echo "<table class='w-full text-sm text-left border-collapse border border-gray-700'>
        <thead class='bg-gray-800 text-white'>
            <tr>";

foreach (array_keys($result[0]) as $col) {
    echo "<th class='px-4 py-2 border border-gray-700'>" . htmlspecialchars($col) . "</th>";
}

echo "</tr></thead><tbody class='bg-gray-900 text-gray-200'>";

foreach ($result as $row) {
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td class='px-4 py-2 border border-gray-700'>" . htmlspecialchars($value ?: '-') . "</td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";         