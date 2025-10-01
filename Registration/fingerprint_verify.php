<?php
header("Content-Type: application/json");

include("../connection.php");

$stmt = $conn->prepare("SELECT id, username, fingerprint FROM users WHERE fingerprint IS NOT NULL");
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result -> fetch_assoc()) {
    $rows[] = [
        'id' => (int)$r['id'],
        'username' => $r['username'],
        'fingerprint' => $r['fingerprint']
    ];
}

echo json_encode(['success' => true, 'data' => $rows]);
