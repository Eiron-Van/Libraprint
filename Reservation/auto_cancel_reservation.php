<?php
// ======================================
// AUTO CANCEL RESERVATIONS (24 HOURS)
// ======================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../connection.php';

// Find expired reservations (older than 24 hours)
$query = "
SELECT r.item_id
FROM reservation r
WHERE r.date_reserved < (NOW() - INTERVAL 2 MINUTE)
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $itemIds = [];

    while ($row = $result->fetch_assoc()) {
        $itemIds[] = $row['item_id'];
    }

    // Update the status of those books back to 'Available'
    $inIds = implode(',', $itemIds);
    $conn->query("UPDATE book_inventory SET status='Available' WHERE item_id IN ($inIds)");

    // Delete expired reservations
    $conn->query("DELETE FROM reservation WHERE date_reserved < (NOW() - INTERVAL 24 HOUR)");

    echo "✅ " . count($itemIds) . " reservation(s) expired and were canceled.\n";
} else {
    echo "No expired reservations found.\n";
}

