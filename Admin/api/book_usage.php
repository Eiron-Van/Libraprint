<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../connection.php';
header('Content-Type: application/json');

// 1️⃣ Total Books in Inventory
$totalBooksQuery = "SELECT COUNT(*) AS total_books FROM book_inventory";
$totalBooks = $conn->query($totalBooksQuery)->fetch_assoc()['total_books'] ?? 0;

// 2️⃣ Books Borrowed (This Month)
$borrowedMonthQuery = "
    SELECT COUNT(*) AS borrowed_books
    FROM borrow_log
    WHERE MONTH(date_borrowed) = MONTH(CURRENT_DATE())
      AND YEAR(date_borrowed) = YEAR(CURRENT_DATE())
";
$borrowedMonth = $conn->query($borrowedMonthQuery)->fetch_assoc()['borrowed_books'] ?? 0;

// 3️⃣ Book Usage Rate
$bookUsageRate = ($totalBooks > 0) ? round(($borrowedMonth / $totalBooks) * 100, 2) : 0;

// 4️⃣ Most Borrowed Books (Top 10)
$topBooksQuery = "
    SELECT b.title, COUNT(l.id) AS borrow_count
    FROM borrow_log l
    JOIN book_inventory b ON l.book_id = b.item_id
    GROUP BY b.title
    ORDER BY borrow_count DESC
    LIMIT 10
";
$topBooksResult = $conn->query($topBooksQuery);
$topBooks = ['labels' => [], 'counts' => []];
while ($row = $topBooksResult->fetch_assoc()) {
    $topBooks['labels'][] = $row['title'];
    $topBooks['counts'][] = (int)$row['borrow_count'];
}

// 5️⃣ Most Borrowed Genres
$genreQuery = "
    SELECT b.class_no AS genre, COUNT(l.id) AS borrow_count
    FROM borrow_log l
    JOIN book_inventory b ON l.book_id = b.item_id
    GROUP BY genre
    ORDER BY borrow_count DESC
";
$genreResult = $conn->query($genreQuery);
$genreData = ['labels' => [], 'counts' => []];
while ($row = $genreResult->fetch_assoc()) {
    $genreData['labels'][] = $row['genre'] ?: 'Unknown';
    $genreData['counts'][] = (int)$row['borrow_count'];
}

echo json_encode([
    'totalBooks' => $totalBooks,
    'borrowedMonth' => $borrowedMonth,
    'usageRate' => $bookUsageRate,
    'topBooks' => $topBooks,
    'genre' => $genreData
]); 