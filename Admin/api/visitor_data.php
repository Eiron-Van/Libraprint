<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../connection.php';
header('Content-Type: application/json');

//  Total Visitors This Month
$totalVisitorsQuery = "
    SELECT COUNT(DISTINCT user_id) AS total_visitors
    FROM login_record
    WHERE MONTH(login_time) = MONTH(CURRENT_DATE())
      AND YEAR(login_time) = YEAR(CURRENT_DATE())
";
$totalVisitors = $conn->query($totalVisitorsQuery)->fetch_assoc()['total_visitors'] ?? 0;

// Daily Attendance Chart
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

//  Purpose Distribution
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

// Gender Breakdown (demographics)
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

// Age Group Breakdown
$ageQuery = "
    SELECT 
        CASE
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 0 AND 12 THEN 'Children (0-12)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 13 AND 21 THEN 'Adolescents (13-21)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 22 AND 35 THEN 'Young Adults (22-35)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 36 AND 59 THEN 'Adults (36-59)'
            ELSE 'Seniors (60+)'
        END AS age_group,
        COUNT(l.id) AS count
    FROM login_record l
    JOIN users u ON l.user_id = u.user_id
    WHERE MONTH(l.login_time) = MONTH(CURRENT_DATE())
    GROUP BY age_group
    ORDER BY FIELD(age_group, 
        'Children (0-12)', 
        'Adolescents (13-21)', 
        'Young Adults (22-35)', 
        'Adults (36-59)', 
        'Seniors (60+)')
";
$ageResult = $conn->query($ageQuery);
$ageData = ['labels' => [], 'counts' => []];
while ($row = $ageResult->fetch_assoc()) {
    $ageData['labels'][] = $row['age_group'];
    $ageData['counts'][] = (int)$row['count'];
}

// Return as JSON
echo json_encode([
    'totalVisitors' => $totalVisitors,
    'daily' => $dailyData,
    'purpose' => $purposeData,
    'gender' => $genderData,
    'age' => $ageData
]);
