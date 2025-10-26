<?php
require('../../connection.php');
require('fpdf186/fpdf.php');

// Get selected month (format: YYYY-MM)
$month = $_POST['month'] ?? date('Y-m');
$monthName = date("F Y", strtotime($month . "-01"));

// ===========================================
// 📊 1. Fetch Monthly Data
// ===========================================

// Total Visitors
$totalVisitorsQuery = "
    SELECT COUNT(DISTINCT user_id) AS total
    FROM login_record
    WHERE DATE_FORMAT(login_time, '%Y-%m') = '$month'
";
$totalVisitors = $conn->query($totalVisitorsQuery)->fetch_assoc()['total'] ?? 0;

// Gender Breakdown
$genderQuery = "
    SELECT u.gender, COUNT(*) AS count
    FROM login_record l
    JOIN users u ON u.id = l.user_id
    WHERE DATE_FORMAT(login_time, '%Y-%m') = '$month'
    GROUP BY u.gender
";
$genderData = $conn->query($genderQuery)->fetch_all(MYSQLI_ASSOC);

// Age Group Breakdown
$ageQuery = "
    SELECT
        CASE
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 0 AND 12 THEN 'Children (0–12)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 13 AND 21 THEN 'Adolescents (13–21)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 22 AND 35 THEN 'Young Adults (22–35)'
            WHEN TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 36 AND 59 THEN 'Adults (36–59)'
            ELSE 'Seniors (60+)'
        END AS age_group,
        COUNT(*) AS count
    FROM login_record l
    JOIN users u ON u.id = l.user_id
    WHERE DATE_FORMAT(l.login_time, '%Y-%m') = '$month'
    GROUP BY age_group
";
$ageData = $conn->query($ageQuery)->fetch_all(MYSQLI_ASSOC);

// Books Read This Month
$booksReadQuery = "
    SELECT COUNT(*) AS total
    FROM book_record
    WHERE DATE_FORMAT(read_date, '%Y-%m') = '$month'
";
$booksRead = $conn->query($booksReadQuery)->fetch_assoc()['total'] ?? 0;

// Most Popular Genre
$genreQuery = "
    SELECT bi.genre, COUNT(*) AS count
    FROM book_record br
    JOIN book_inventory bi ON br.book_id = bi.item_id
    WHERE DATE_FORMAT(br.read_date, '%Y-%m') = '$month'
    GROUP BY bi.genre
    ORDER BY count DESC
    LIMIT 1
";
$genreResult = $conn->query($genreQuery);
$popularGenre = $genreResult->num_rows > 0 ? $genreResult->fetch_assoc()['genre'] : '—';

// ===========================================
// 🧾 2. Generate PDF
// ===========================================
$pdf = new FPDF();
$pdf->AddPage();

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Lucena City Library Monthly Report", 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Reporting Period: $monthName", 0, 1, 'C');
$pdf->Ln(8);

// Summary
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Summary Overview", 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, "• Total Visitors: $totalVisitors", 0, 1);
$pdf->Cell(0, 8, "• Books Read: $booksRead", 0, 1);
$pdf->Cell(0, 8, "• Most Popular Genre: $popularGenre", 0, 1);
$pdf->Ln(6);

// Gender Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Visitor Gender Breakdown", 0, 1);
$pdf->SetFont('Arial', '', 12);
foreach ($genderData as $g) {
    $pdf->Cell(0, 8, "• {$g['gender']}: {$g['count']}", 0, 1);
}
$pdf->Ln(6);

// Age Group Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Visitor Age Group Breakdown", 0, 1);
$pdf->SetFont('Arial', '', 12);
foreach ($ageData as $a) {
    $pdf->Cell(0, 8, "• {$a['age_group']}: {$a['count']}", 0, 1);
}
$pdf->Ln(10);

// Footer
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, "Generated automatically by LibraPrint System", 0, 1, 'C');

$filename = "LucenaCityLibrary_Report_" . str_replace("-", "_", $month) . ".pdf";
$pdf->Output("D", $filename);