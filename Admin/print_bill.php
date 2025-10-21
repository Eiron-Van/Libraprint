<?php
include '../../connection.php';

$borrow_id = $_GET['borrow_id'] ?? 0;

$query = $conn->prepare("
    SELECT o.*, u.first_name, u.last_name, b.title
    FROM overdue_log o
    JOIN users u ON o.user_id = u.id
    JOIN book_inventory b ON o.book_id = b.item_id
    WHERE o.borrow_id = ?
");
$query->bind_param("i", $borrow_id);
$query->execute();
$bill = $query->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Overdue Bill</title>
    <style>
        body { font-family: Arial; text-align: center; }
        .bill { border: 1px solid #000; padding: 20px; width: 400px; margin: auto; }
    </style>
</head>
<body onload="window.print()">
    <div class="bill">
        <h2>Library Overdue Bill</h2>
        <p><strong>Name:</strong> <?= $bill['first_name'] . ' ' . $bill['last_name'] ?></p>
        <p><strong>Book Title:</strong> <?= $bill['title'] ?></p>
        <p><strong>Days Overdue:</strong> <?= $bill['days_overdue'] ?> day(s)</p>
        <p><strong>Total Fine:</strong> ₱<?= $bill['days_overdue'] * 1 ?></p>
        <p><strong>Status:</strong> <?= $bill['status'] ?></p>
        <p><em>Date Detected: <?= $bill['date_overdue_detected'] ?></em></p>
    </div>
</body>
</html>
