<?php
include '../../connection.php';
require_once __DIR__ . '../../inc/auth_admin.php';
header('Content-Type: application/json');

// 1️⃣ Total Books in Inventory
$totalBooksQuery = "SELECT COUNT(*) AS total_books FROM book_inventory";
$totalBooks = $conn->query($totalBooksQuery)->fetch_assoc()['total_books'] ?? 0;

// 2️⃣ Books Read (This Month)
$readMonthQuery = "
    SELECT COUNT(*) AS read_books
    FROM book_record
    WHERE MONTH(read_date) = MONTH(CURRENT_DATE())
      AND YEAR(read_date) = YEAR(CURRENT_DATE())
";
$readMonth = $conn->query($readMonthQuery)->fetch_assoc()['read_books'] ?? 0;

// 3️⃣ Book Usage Rate
$bookUsageRate = ($totalBooks > 0) ? round(($readMonth / $totalBooks) * 100, 2) : 0;

// 4️⃣ Most Read Books (Top 10)
$topBooksQuery = "
    SELECT b.title, COUNT(l.id) AS read_count
    FROM book_record l
    JOIN book_inventory b ON l.book_id = b.item_id
    GROUP BY b.title
    ORDER BY read_count DESC
    LIMIT 10
";
$topBooksResult = $conn->query($topBooksQuery);
$topBooks = ['labels' => [], 'counts' => []];
while ($row = $topBooksResult->fetch_assoc()) {
    $topBooks['labels'][] = $row['title'];
    $topBooks['counts'][] = (int)$row['read_count'];
}

// 5️⃣ Most Borrowed Genres
$genreQuery = "
    SELECT b.genre AS genre, COUNT(l.id) AS read_count
    FROM book_record l
    JOIN book_inventory b ON l.book_id = b.item_id
    GROUP BY genre
    ORDER BY read_count DESC
";
$genreResult = $conn->query($genreQuery);
$genreData = ['labels' => [], 'counts' => []];
while ($row = $genreResult->fetch_assoc()) {
    $genreData['labels'][] = $row['genre'] ?: 'Unknown';
    $genreData['counts'][] = (int)$row['read_count'];
}

echo json_encode([
    'totalBooks' => $totalBooks,
    'readMonth' => $readMonth,
    'usageRate' => $bookUsageRate,
    'topBooks' => $topBooks,
    'genre' => $genreData
]); 