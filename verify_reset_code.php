<?php
require 'config.php';
header('Content-Type: application/json');

$data   = json_decode(file_get_contents('php://input'), true);
$uid    = $data['user_id'] ?? '';
$code   = $data['code']    ?? '';

if (!$uid || !$code) {
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE id=? AND reset_code=? AND reset_exp > NOW()");
$stmt->execute([$uid, $code]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['error' => 'Invalid or expired code']);
    exit;
}

echo json_encode(['success' => true, 'user_id' => $uid]);
?>