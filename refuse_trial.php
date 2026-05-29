<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]); exit;
}

$pdo->prepare("UPDATE users SET trial_refused = 1 WHERE id = ?")
    ->execute([$_SESSION['user_id']]);

echo json_encode(['success' => true]);
?>