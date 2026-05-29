<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.html'); exit; 
}

$uid = $_SESSION['user_id'];

// جيب بيانات المستخدم كاملة
$stmt = $pdo->prepare("
    SELECT status, trial_used, trial_refused, trial_started_at 
    FROM users WHERE id = ?
");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// حساب غير مقبول
if (!$user || in_array($user['status'], ['pending', 'rejected'])) {
    header('Location: agent_pending.php'); exit;
}

// جيب آخر subscription
$sub = $pdo->prepare("
    SELECT status, end_date 
    FROM subscriptions 
    WHERE user_id = ? 
    ORDER BY created_at DESC LIMIT 1
");
$sub->execute([$uid]);
$subscription = $sub->fetch();

// 1. subscription مدفوعة ومقبولة وما انتهت → داشبورد مفتوح
if ($subscription && 
    $subscription['status'] === 'approved' && 
    !empty($subscription['end_date']) && 
    strtotime($subscription['end_date']) > time()) {
    header('Location: agents.php'); exit;
}

// 2. دفع وينتظر قبول الأدمين → رسالة انتظار
if ($subscription && $subscription['status'] === 'pending') {
    header('Location: agents.php?payment=pending'); exit;
}

// 3. trial active وما انتهى → داشبورد مفتوح
if ($user['trial_used'] && !$user['trial_refused'] && !empty($user['trial_started_at'])) {
    $elapsed = time() - strtotime($user['trial_started_at']);
    if ($elapsed < 7 * 24 * 3600) {
        header('Location: agents.php'); exit;
    }
}

// 4. كل الحالات الباقية → agents.php والداشبورد بلوكد
header('Location: agents.php'); exit;
?>