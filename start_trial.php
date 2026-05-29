<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]); exit;
}

$uid = $_SESSION['user_id'];

// تحقق إذا ما استعمل التجربة قبل
$check = $pdo->prepare("SELECT trial_used, trial_started_at FROM users WHERE id = ?");
$check->execute([$uid]);
$user = $check->fetch();
if ($user['trial_refused']) {
    echo json_encode(['success' => false, 'reason' => 'refused']);
    exit;
}
if ($user['trial_used']) {
    // حسب الوقت المتبقي
    $elapsed = time() - strtotime($user['trial_started_at']);
    $remaining = max(0, (7 * 24 * 3600) - $elapsed);
    echo json_encode(['success' => true, 'remaining_seconds' => $remaining]);
    exit;
}

// أول مرة — سجّل وقت البداية
$pdo->prepare("UPDATE users SET trial_used = 1, trial_started_at = NOW() WHERE id = ?")
    ->execute([$uid]);

// أضف subscription trial
$pdo->prepare("INSERT INTO subscriptions (user_id, amount, status, plan, start_date, end_date, created_at) 
               VALUES (?, 0, 'approved', 'Trial', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), NOW())")
    ->execute([$uid]);

echo json_encode([
    'success' => true,
    'remaining_seconds' => 7 * 24 * 3600
]);
?>