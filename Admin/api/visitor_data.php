<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Libraprint/connection.php';
header('Content-Type: application/json');

// 1️⃣ Total Visitors This Month
$totalVisitorsQuery = "
    SELECT COUNT(DISTINCT user_id) AS total_visitors
    FROM login_record
    WHERE MONTH(login_time) = MONTH(CURRENT_DATE())
      AND YEAR(login_time) = YEAR(CURRENT_DATE())
";
$totalVisitors = $conn->query($totalVisitorsQuery)->fetch_assoc()['total_visitors'] ?? 0;

// 2️⃣ Daily Attendance Chart
$dailyQuery = "
    SELECT DATE(login_time) AS date, COUNT(*) AS count
    FROM login_record
    WHERE MONTH(login_time) = MONTH(CURRENT_DATE())
    GROUP BY DATE(login_time)
    ORDER BY DATE(login_time)
";
$dailyResult = $conn->query($dailyQuery);
$dailyData = ['dates' => [], 'counts' => []];
while ($row = $dailyResult->fetch_assoc()) {
    $dailyData['dates'][] = $row['date'];
    $dailyData['counts'][] = (int)$row['count'];
}

// 3️⃣ Purpose Distribution
$purposeQuery = "
    SELECT purpose, COUNT(*) AS count
    FROM login_record
    WHERE MONTH(login_time) = MONTH(CURRENT_DATE())
    GROUP BY purpose
";
$purposeResult = $conn->query($purposeQuery);
$purposeData = ['labels' => [], 'counts' => []];
while ($row = $purposeResult->fetch_assoc()) {
    $purposeData['labels'][] = $row['purpose'];
    $purposeData['counts'][] = (int)$row['count'];
}

// 4️⃣ Gender Breakdown (demographics)
$genderQuery = "
    SELECT u.gender, COUNT(l.id) AS count
    FROM login_record l
    JOIN users u ON l.user_id = u.user_id
    WHERE MONTH(l.login_time) = MONTH(CURRENT_DATE())
    GROUP BY u.gender
";
$genderResult = $conn->query($genderQuery);
$genderData = ['labels' => [], 'counts' => []];
while ($row = $genderResult->fetch_assoc()) {
    $genderData['labels'][] = $row['gender'];
    $genderData['counts'][] = (int)$row['count'];
}

// Return as JSON
echo json_encode([
    'totalVisitors' => $totalVisitors,
    'daily' => $dailyData,
    'purpose' => $purposeData,
    'gender' => $genderData
]);
