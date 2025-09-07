<?php
include '../../connection.php';
$id = $_GET['id'];
$sql = "SELECT * FROM book_inventory WHERE id=$id";
$result = $conn->query($sql);
echo json_encode($result->fetch_assoc());
?>
