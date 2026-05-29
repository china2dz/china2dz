<?php
ob_start();
require 'config.php';
header('Content-Type: application/json');

$data  = json_decode(file_get_contents('php://input'), true);
$uid   = $data['user_id']  ?? '';
$pass  = $data['password'] ?? '';

if (!$uid || !$pass) {
    ob_end_clean();
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE id=?");
$stmt->execute([$uid]);
$row = $stmt->fetch();

if (!$row) {
    ob_end_clean();
    echo json_encode(['error' => 'User not found']);
    exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password_hash=?, reset_code=NULL, reset_exp=NULL WHERE id=?")
    ->execute([$hash, $uid]);

ob_end_clean();
echo json_encode(['success' => true]);
?>