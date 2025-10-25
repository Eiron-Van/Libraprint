<?php
require '../../connection.php';

// 1️⃣ Get transactions grouped by user and date, plus age group
$sql = "
    SELECT 
        u.user_id,
        CASE
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 0 AND 12 THEN 'Children (0-12)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 13 AND 21 THEN 'Adolescents (13-21)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 22 AND 35 THEN 'Young Adults (22-35)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 36 AND 59 THEN 'Adults (36-59)'
            ELSE 'Seniors (60+)'
        END AS age_group,
        DATE(br.read_date) AS read_date,
        GROUP_CONCAT(DISTINCT bi.genre ORDER BY bi.genre ASC SEPARATOR ',') AS genres
    FROM book_record br
    JOIN book_inventory bi ON br.book_id = bi.item_id
    JOIN users u ON br.user_id = u.user_id
    WHERE bi.genre IS NOT NULL AND bi.genre != ''
    GROUP BY u.user_id, DATE(br.read_date)
";

$result = $conn->query($sql);

$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$row['age_group']][] = explode(',', $row['genres']);
}

echo json_encode(['age_groups' => $grouped]);