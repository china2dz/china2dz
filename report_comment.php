<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentType = $data['comment_type'] ?? '';
$commentId   = intval($data['comment_id'] ?? 0);
$commentText = $data['comment_text'] ?? '';
$reason      = $data['reason'] ?? '';
$pageUrl     = $data['page_url'] ?? '';
$reporterId  = $_SESSION['user_id'];

if (!$commentType || !$commentId || !$reason) {
    echo json_encode(['error' => 'Missing data']); exit;
}

// احفظ الـ report
$pdo->prepare("INSERT INTO comment_reports (reporter_id, comment_type, comment_id, comment_text, reason, page_url) VALUES (?,?,?,?,?,?)")
    ->execute([$reporterId, $commentType, $commentId, $commentText, $reason, $pageUrl]);

// جيب اسم المُبلِّغ
$s = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$s->execute([$reporterId]);
$reporter = $s->fetch(PDO::FETCH_ASSOC);
$reporterName = trim(($reporter['first_name']??'') . ' ' . ($reporter['last_name']??''));

// جيب الأدمن
$s = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$s->execute();
$admin = $s->fetch(PDO::FETCH_ASSOC);
if ($admin) {
    $typeLabel = ucfirst(str_replace('_', ' ', $commentType));
    
    // جيب اسم صاحب التعليق
    $authorName = '';
    if ($commentType === 'index_review') {
        $a = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = (SELECT user_id FROM reviews WHERE id = ?)");
        $a->execute([$commentId]);
        $author = $a->fetch(PDO::FETCH_ASSOC);
        $authorName = trim(($author['first_name']??'') . ' ' . ($author['last_name']??''));
    } elseif ($commentType === 'dealer_review') {
        $a = $pdo->prepare("SELECT reviewer_name FROM agent_reviews WHERE id = ?");
        $a->execute([$commentId]);
        $author = $a->fetch(PDO::FETCH_ASSOC);
        $authorName = $author['reviewer_name'] ?? '';
    }
    
    $byAuthor = $authorName ? " by {$authorName}" : '';
    
    $pdo->prepare("INSERT INTO notifications (user_id, sender_id, title, message, type) VALUES (?,?,'Reviews reported',?,'warning')")
        ->execute([
            $admin['id'],
            $reporterId,
            "{$reporterName} reported a review{$byAuthor}: \"{$commentText}\" — Reason: {$reason}"
        ]);
}

echo json_encode(['success' => true]);
?>