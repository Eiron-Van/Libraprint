<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../connection.php';
header('Content-Type: application/json');

// 1️⃣ Monthly Reading Trend
$monthlyQuery = "
    SELECT DATE_FORMAT(read_date, '%b') AS month, COUNT(*) AS count
    FROM book_record
    WHERE YEAR(read_date) = YEAR(CURRENT_DATE())
    GROUP BY MONTH(read_date)
    ORDER BY MONTH(read_date)
";
$monthlyResult = $conn->query($monthlyQuery);
$monthlyData = ['labels' => [], 'counts' => []];
while ($row = $monthlyResult->fetch_assoc()) {
    $monthlyData['labels'][] = $row['month'];
    $monthlyData['counts'][] = (int)$row['count'];
}

// 2️⃣ Quarterly Comparison
$quarterQuery = "
    SELECT QUARTER(read_date) AS quarter, COUNT(*) AS count
    FROM book_record
    WHERE YEAR(read_date) = YEAR(CURRENT_DATE())
    GROUP BY quarter
    ORDER BY quarter
";
$quarterResult = $conn->query($quarterQuery);
$quarterData = ['labels' => [], 'counts' => []];
while ($row = $quarterResult->fetch_assoc()) {
    $quarterData['labels'][] = 'Q' . $row['quarter'];
    $quarterData['counts'][] = (int)$row['count'];
}

// 3️⃣ Read vs Reservation Ratio
$borrowQuery = "
    SELECT 
        (SELECT COUNT(*) FROM book_record WHERE YEAR(read_date)=YEAR(CURRENT_DATE())) AS read_count,
        (SELECT COUNT(*) FROM borrow_log WHERE YEAR(date_borrowed)=YEAR(CURRENT_DATE())) AS borrow_count
";
$borrowResult = $conn->query($borrowQuery);
if ($borrowResult) {
    $borrowRow = $borrowResult->fetch_assoc();
    $readCount = (int)$borrowRow['read_count'];
    $borrowCount = (int)$borrowRow['borrow_count'];
} else {
    $readCount = 0;
    $borrowCount = 0;
}

// 4️⃣ Average Monthly Reading Sessions
$avgMonthlyQuery = "
    SELECT ROUND(AVG(monthly_reads), 2) AS avg_monthly_reads FROM (
        SELECT COUNT(*) AS monthly_reads
        FROM book_record
        WHERE YEAR(read_date) = YEAR(CURRENT_DATE())
        GROUP BY MONTH(read_date)
    ) AS sub
";
$avgMonthly = round($conn->query($avgMonthlyQuery)->fetch_assoc()['avg_monthly_reads'] ?? 0, 2);

echo json_encode([
    'monthly' => $monthlyData,
    'quarterly' => $quarterData,
    'readCount' => $readCount,
    'borrowCount' => $borrowCount,
    'avgMonthlyReads' => $avgMonthly
]);