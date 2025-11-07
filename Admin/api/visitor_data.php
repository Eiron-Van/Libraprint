<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
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

// 2️⃣ Books Read (this month)
$booksReadQuery = "
    SELECT COUNT(id) AS total
    FROM book_record
    WHERE MONTH(read_date) = MONTH(CURRENT_DATE())
    AND YEAR(read_date) = YEAR(CURRENT_DATE())
";
$booksRead = $conn->query($booksReadQuery)->fetch_assoc()['total'] ?? 0;

// 3️⃣ Most Popular Genre
$popularGenreQuery = "
    SELECT bi.genre, COUNT(*) AS count
    FROM book_record br
    JOIN book_inventory bi ON br.book_id = bi.item_id
    WHERE MONTH(br.read_date) = MONTH(CURRENT_DATE())
    AND YEAR(br.read_date) = YEAR(CURRENT_DATE())
    GROUP BY bi.genre
    ORDER BY count DESC
    LIMIT 1
";
$genreResult = $conn->query($popularGenreQuery);
$popularGenre = $genreResult->num_rows > 0 ? $genreResult->fetch_assoc()['genre'] : '—';

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

// Monthly Attendance Summary
$monthlyQuery = "
    SELECT 
        MONTHNAME(login_time) AS month,
        COUNT(id) AS total
    FROM login_record
    WHERE YEAR(login_time) = YEAR(CURRENT_DATE())
    GROUP BY MONTH(login_time)
    ORDER BY MONTH(login_time)
";
$monthlyResult = $conn->query($monthlyQuery);
$monthlyData = ['labels' => [], 'counts' => []];
while ($row = $monthlyResult->fetch_assoc()) {
    $monthlyData['labels'][] = $row['month'];
    $monthlyData['counts'][] = (int)$row['total'];
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
    SELECT 
        IFNULL(u.gender, 'Unknown') AS gender,
        COUNT(l.id) AS count
    FROM login_record l
    LEFT JOIN users u ON l.user_id = u.id
    WHERE MONTH(l.login_time) = MONTH(CURRENT_DATE())
      AND YEAR(l.login_time) = YEAR(CURRENT_DATE())
    GROUP BY gender
    ORDER BY FIELD(gender, 
        'Male', 'Female', 'Lesbian', 'Gay', 'Bisexual', 
        'Transgender', 'Queer/Questioning', 'Other', 'Unknown')
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
    JOIN users u ON l.user_id = u.id
    WHERE MONTH(l.login_time) = MONTH(CURRENT_DATE())
    GROUP BY age_group
    ORDER BY FIELD(age_group, 
        'Children (12)', 
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
    'booksRead' => $booksRead,
    'popularGenre' => $popularGenre,
    'daily' => $dailyData,
    'monthly' => $monthlyData,
    'purpose' => $purposeData,
    'gender' => $genderData,
    'age' => $ageData
]);
