<?php
require '../../connection.php';

// 1️⃣ Fetch book reading data grouped by user_id and date
$sql = "
    SELECT 
        br.user_id,
        DATE(br.read_date) AS read_date,
        GROUP_CONCAT(DISTINCT bi.genre ORDER BY bi.genre ASC SEPARATOR ',') AS genres
    FROM book_record br
    JOIN book_inventory bi ON br.book_id = bi.item_id
    WHERE bi.genre IS NOT NULL AND bi.genre != ''
    GROUP BY br.user_id, DATE(br.read_date)
";
$result = $conn->query($sql);

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = explode(',', $row['genres']);
}

// Encode transactions to JSON (for Apriori processing)
echo json_encode(['transactions' => $transactions]);