<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '/connection.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid book ID.");
}

$sql = "DELETE FROM book_inventory WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php");
exit;
?>
