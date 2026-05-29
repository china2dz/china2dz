<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'unauthorized']); exit;
}

header('Content-Type: application/json');

$data   = json_decode(file_get_contents('php://input'), true);
$type   = $data['type']   ?? '';
$id     = (int)($data['id'] ?? 0);
$action = $data['action'] ?? '';
$trial  = $data['trial']  ?? false;
// قبول/رفض وسيط
if ($type === 'agent' && in_array($action, ['approved','rejected','blocked','temp_blocked','unblocked'])) {
 if ($action === 'temp_blocked') {
    $days = (int)($data['days'] ?? 3);
    $reason = $data['reason'] ?? '';
    $until = date('Y-m-d H:i:s', strtotime("+{$days} days"));
    $pdo->prepare("UPDATE users SET status='blocked', blocked_until=?, block_reason=? WHERE id=?")
        ->execute([$until, $reason, $id]);
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")
        ->execute([$id, 'Account Temporarily Suspended', 
                   'Your account has been suspended for '.$days.' days. Reason: '.$reason, 'warning']);
} elseif ($action === 'unblocked') {
    $pdo->prepare("UPDATE users SET status='active', blocked_until=NULL, block_reason=NULL WHERE id=?")
        ->execute([$id]);
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")
        ->execute([$id, 'Account Reactivated ✅', 'Your account has been reactivated.', 'success']);
} else {
    $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$action, $id]);
}
    
    echo json_encode(['success' => true]);
    exit;
}
// قبول/رفض دفع
if ($type === 'payment' && in_array($action, ['approved','rejected'])) {
    $pdo->prepare("UPDATE subscriptions SET status=? WHERE id=?")->execute([$action, $id]);
    
    if ($action === 'approved') {
        // جلب user_id من الـ subscription
        $stmt = $pdo->prepare("SELECT user_id FROM subscriptions WHERE id=?");
        $stmt->execute([$id]);
        $sub = $stmt->fetch();
        
        if ($sub) {
            // تفعيل الحساب + تحديد تاريخ انتهاء الاشتراك
            $pdo->prepare("UPDATE users SET status='approved' WHERE id=?")->execute([$sub['user_id']]);
            $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")
    ->execute([
        $sub['user_id'],
        'Subscription Approved! ✅',
        'Your subscription has been approved. You now have full access to China2DZ.',
        'success'
    ]);
            $stmt2 = $pdo->prepare("SELECT amount FROM subscriptions WHERE id=?");
$stmt2->execute([$id]);
$subRow = $stmt2->fetch();
$amount = (int)($subRow['amount'] ?? 0);

// حساب الأيام حسب المبلغ
if ($amount <= 1900) $days = 30;
elseif ($amount <= 4900) $days = 30;
elseif ($amount <= 8500) $days = 180;
elseif ($amount <= 9900) $days = 90;
elseif ($amount <= 12900) $days = 90;
elseif ($amount <= 17900) $days = 180;
elseif ($amount <= 22000) $days = 180;
elseif ($amount <= 32000) $days = 365;
else $days = 30;

$pdo->prepare("UPDATE subscriptions SET start_date=NOW(), end_date=DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id=?")
    ->execute([$days, $id]);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}
// حل التبليغ
if ($type === 'report' && $action === 'resolved') {
    $pdo->prepare("UPDATE agent_reports SET status='resolved' WHERE id=?")->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}
// ═══════════════ GET CLIENT COMMENTS ═══════════════
if ($data['type'] === 'get_client_comments') {
    $uid = intval($data['id']);
    $comments = [];

    // Index reviews
    $s = $pdo->prepare("SELECT id, content AS text, created_at, 'index_review' AS type, 'Index Review' AS source FROM reviews WHERE user_id = ?");
    $s->execute([$uid]);
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) $comments[] = $r;

    // Car reviews
    $s = $pdo->prepare("SELECT cr.id, cr.review_text AS text, cr.created_at, 'car_review' AS type, CONCAT('Car Review — ', c.title) AS source FROM car_reviews cr LEFT JOIN cars c ON c.id = cr.car_id WHERE cr.user_id = ? AND cr.parent_id IS NULL");
    $s->execute([$uid]);
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) $comments[] = $r;

    // Car replies
    $s = $pdo->prepare("SELECT cr.id, cr.review_text AS text, cr.created_at, 'car_reply' AS type, CONCAT('Car Reply — ', c.title) AS source FROM car_reviews cr LEFT JOIN cars c ON c.id = cr.car_id WHERE cr.user_id = ? AND cr.parent_id IS NOT NULL");
    $s->execute([$uid]);
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) $comments[] = $r;

    // Dealer reviews
    $s = $pdo->prepare("SELECT ar.id, ar.comment AS text, ar.created_at, 'dealer_review' AS type, CONCAT('Dealer Review — ', u.first_name, ' ', u.last_name) AS source FROM agent_reviews ar LEFT JOIN users u ON u.id = ar.agent_id WHERE ar.reviewer_id = ?");
    $s->execute([$uid]);
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) $comments[] = $r;

    // Dealer replies
    $s = $pdo->prepare("SELECT dr.id, dr.content AS text, dr.created_at, 'dealer_reply' AS type, 'Dealer Reply' AS source FROM dealer_review_replies dr WHERE dr.user_id = ?");
    $s->execute([$uid]);
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) $comments[] = $r;

    usort($comments, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));

// جلب التعليقات المحذوفة
$deleted = [];
$s = $pdo->prepare("SELECT cdn.comment_type AS type, cdn.comment_text AS text, cdn.deleted_at, cdn.comment_type AS source, u.first_name, u.last_name, u.profile_photo FROM client_deleted_notifications cdn LEFT JOIN users u ON u.id = cdn.user_id WHERE cdn.user_id = ? ORDER BY cdn.deleted_at DESC");
$s->execute([$uid]);
foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $r['deleted'] = true;
    $r['id'] = 0;
    $r['created_at'] = $r['deleted_at'];
    $r['source'] = ucfirst(str_replace('_', ' ', $r['type']));
$r['user_name'] = trim(($r['first_name']??'') . ' ' . ($r['last_name']??''));
    $deleted[] = $r;
}

foreach ($comments as &$c) $c['deleted'] = false;

echo json_encode(['comments' => array_merge($comments, $deleted)]);
exit;
}

// ═══════════════ DELETE CLIENT COMMENT ═══════════════
if ($data['type'] === 'delete_comment') {
    $commentType = $data['comment_type'];
    $commentId = intval($data['comment_id']);
    $clientId  = intval($data['client_id']);

    $text = '';
    $source = '';

    if ($commentType === 'index_review') {
        $s = $pdo->prepare("SELECT content FROM reviews WHERE id = ?");
        $s->execute([$commentId]);
        $text = $s->fetchColumn();
        $source = 'your review on the homepage';
        $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$commentId]);
    }
    elseif ($commentType === 'car_review') {
        $s = $pdo->prepare("SELECT review_text FROM car_reviews WHERE id = ?");
        $s->execute([$commentId]);
        $text = $s->fetchColumn();
        $source = 'your car review';
        $pdo->prepare("DELETE FROM car_reviews WHERE id = ?")->execute([$commentId]);
    }
    elseif ($commentType === 'car_reply') {
        $s = $pdo->prepare("SELECT review_text FROM car_reviews WHERE id = ?");
        $s->execute([$commentId]);
        $text = $s->fetchColumn();
        $source = 'your reply on a car listing';
        $pdo->prepare("DELETE FROM car_reviews WHERE id = ?")->execute([$commentId]);
    }
    elseif ($commentType === 'dealer_review') {
        $s = $pdo->prepare("SELECT comment FROM agent_reviews WHERE id = ?");
        $s->execute([$commentId]);
        $text = $s->fetchColumn();
        $source = 'your dealer review';
        $pdo->prepare("DELETE FROM agent_reviews WHERE id = ?")->execute([$commentId]);
    }
    elseif ($commentType === 'dealer_reply') {
        $s = $pdo->prepare("SELECT content FROM dealer_review_replies WHERE id = ?");
        $s->execute([$commentId]);
        $text = $s->fetchColumn();
        $source = 'your reply on a dealer review';
        $pdo->prepare("DELETE FROM dealer_review_replies WHERE id = ?")->execute([$commentId]);
    }

    // Save to log
    $pdo->prepare("INSERT INTO client_deleted_notifications (user_id, comment_type, comment_text) VALUES (?,?,?)")
        ->execute([$clientId, $commentType, $text]);
$notifMsg = "Your comment has been removed by the admin: \"" . mb_substr($text ?? '', 0, 80) . "\"";
$pdo->prepare("INSERT INTO notifications (user_id, sender_id, title, message, type) VALUES (?,NULL,'Comment Removed',?,'warning')")
    ->execute([$clientId, $notifMsg]);
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['error' => 'invalid request']);
?>