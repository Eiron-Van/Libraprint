<?php
require '../../connection.php';
require 'helpers.php';

$sql = "
    SELECT 
        u.user_id,
        CONCAT(u.first_name, ' ', u.last_name) AS name,
        d.total_overdue_books,
        d.logged_at
    FROM delinquent_log AS d
    JOIN users AS u ON d.user_id = u.user_id
    ORDER BY d.total_overdue_books DESC, d.logged_at DESC
";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<div class='text-center text-gray-400 mt-10'>No delinquent borrowers found.</div>";
    exit;
}

echo "
<div class='overflow-auto rounded-lg shadow-lg shadow-gray-500/30 max-h-[56vh]'>
    <div class='grid grid-cols-3 p-2 bg-gray-700 sticky top-0 text-center'>
        <div class='col-span-1'>Borrower Name</div>
        <div class='col-span-1'>Total Overdue Books</div>
        <div class='col-span-1'>Last Logged</div>
    </div>
";

while ($row = $result->fetch_assoc()) {
    echo "
    <div class='grid grid-cols-3 p-2 bg-gray-200 text-center text-sm text-gray-600 border-b border-gray-300 hover:bg-gray-100 transition'>
        <div class='col-span-1'>" . htmlspecialchars($row['name']) . "</div>
        <div class='col-span-1'>" . htmlspecialchars($row['total_overdue_books']) . "</div>
        <div class='col-span-1'>" . formatDateTime($row['logged_at']) . "</div>
    </div>
    ";
}

echo "</div>";
