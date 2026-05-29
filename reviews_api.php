<?php
session_start();
ini_set('display_errors', 0);
error_reporting(0);
require_once 'config.php';
header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

function getUserFromToken($pdo, $token) {
    $st = $pdo->prepare("SELECT user_id FROM user_sessions WHERE session_token=? AND expires_at > NOW()");
    $st->execute([$token]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['user_id'] : null;
}

// ─── GET REVIEWS ───────────────────────────────────────────────
if ($action === 'get') {
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = 3;
    $offset = ($page - 1) * $limit;
    $uid    = $userId ?? 0;
    $stmt = $pdo->prepare("
        SELECT r.id, r.rating, r.content, r.created_at,
               u.first_name, u.last_name, u.id AS uid, u.profile_photo,
               (SELECT COUNT(*) FROM review_likes_index WHERE review_id = r.id) AS likes,
               (SELECT COUNT(*) FROM review_likes_index WHERE review_id = r.id AND user_id = ?) AS user_liked
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $uid, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($reviews as &$rev) {
        $s2 = $pdo->prepare("
            SELECT rp.id, rp.content, rp.created_at, rp.user_id, u.first_name, u.last_name, u.profile_photo
            FROM review_replies rp
            JOIN users u ON u.id = rp.user_id
            WHERE rp.review_id = ?
            ORDER BY rp.created_at ASC
        ");
        $s2->execute([$rev['id']]);
        $rev['replies'] = $s2->fetchAll(PDO::FETCH_ASSOC);
    }
    $total = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    echo json_encode(['success'=>true,'reviews'=>$reviews,'total'=>(int)$total,'has_more'=>($offset+$limit)<$total]);
    exit;
}

// ─── ADD REVIEW ────────────────────────────────────────────────
if ($action === 'add') {
    if (!$userId) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
    $data    = json_decode(file_get_contents('php://input'), true);
    $content = trim($data['content'] ?? '');
    $rating  = (int)($data['rating'] ?? 5);
    if (!$content || $rating < 1 || $rating > 5) { echo json_encode(['success'=>false,'message'=>'Invalid data']); exit; }
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, rating, content) VALUES (?,?,?)");
    $stmt->execute([$userId, $rating, $content]);
    echo json_encode(['success'=>true,'message'=>'Review added']);
    exit;
}

// ─── TOGGLE LIKE ───────────────────────────────────────────────
if ($action === 'like') {
    if (!$userId) {
        $token = $_POST['token'] ?? '';
        if ($token) $userId = getUserFromToken($pdo, $token);
    }
    if (!$userId) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
    $reviewId = (int)($_POST['review_id'] ?? $_GET['review_id'] ?? 0);
    if (!$reviewId) { echo json_encode(['success'=>false]); exit; }
    $check = $pdo->prepare("SELECT id FROM review_likes_index WHERE review_id=? AND user_id=?");
    $check->execute([$reviewId, $userId]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM review_likes_index WHERE review_id=? AND user_id=?")->execute([$reviewId,$userId]);
        $liked = false;
    } else {
        $pdo->prepare("INSERT INTO review_likes_index (review_id,user_id) VALUES (?,?)")->execute([$reviewId,$userId]);
        $liked = true;
        $reviewOwner = $pdo->prepare("SELECT user_id, content FROM reviews WHERE id = ?");
        $reviewOwner->execute([$reviewId]);
        $ownerRow = $reviewOwner->fetch(PDO::FETCH_ASSOC);
        if ($ownerRow && $ownerRow['user_id'] != $userId) {
            $liker = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $liker->execute([$userId]);
            $likerRow = $liker->fetch(PDO::FETCH_ASSOC);
            $likerName = trim(($likerRow['first_name']??'').' '.($likerRow['last_name']??''));
            $pdo->prepare("INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at) VALUES (?,?,?,?,'like',?,NOW())")
                ->execute([$ownerRow['user_id'],$userId,$likerName.' liked your review',$ownerRow['content'],'index.php?scrollreply=like#rev_'.$reviewId]);
        }
    }
    $count = $pdo->prepare("SELECT COUNT(*) FROM review_likes_index WHERE review_id=?");
    $count->execute([$reviewId]);
    echo json_encode(['success'=>true,'liked'=>$liked,'count'=>(int)$count->fetchColumn()]);
    exit;
}

// ─── ADD REPLY ─────────────────────────────────────────────────
if ($action === 'reply') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$userId) {
        $token = $data['token'] ?? '';
        if ($token) $userId = getUserFromToken($pdo, $token);
    }
    if (!$userId) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
    $reviewId = (int)($data['review_id'] ?? 0);
    $content  = trim($data['content'] ?? '');
    if (!$reviewId || !$content) { echo json_encode(['success'=>false]); exit; }
    $stmt = $pdo->prepare("INSERT INTO review_replies (review_id,user_id,content) VALUES (?,?,?)");
    $stmt->execute([$reviewId, $userId, $content]);
    $reviewOwner2 = $pdo->prepare("SELECT user_id, content FROM reviews WHERE id = ?");
    $reviewOwner2->execute([$reviewId]);
    $ownerRow2 = $reviewOwner2->fetch(PDO::FETCH_ASSOC);
    if ($ownerRow2 && $ownerRow2['user_id'] != $userId) {
        $replier2 = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $replier2->execute([$userId]);
        $replierRow2 = $replier2->fetch(PDO::FETCH_ASSOC);
        $replierName2 = trim(($replierRow2['first_name']??'').' '.($replierRow2['last_name']??''));
        $pdo->prepare("INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at) VALUES (?,?,?,?,'comment',?,NOW())")
            ->execute([$ownerRow2['user_id'],$userId,$replierName2.' replied to your review',$content,'index.php?scrollreply=1#rev_'.$reviewId]);
    }
    $user = $pdo->prepare("SELECT first_name,last_name FROM users WHERE id=?");
    $user->execute([$userId]);
    $u = $user->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'name'=>$u['first_name'].' '.$u['last_name'],'content'=>$content,'created_at'=>date('Y-m-d H:i:s')]);
    exit;
}

// ─── DELETE REVIEW ─────────────────────────────────────────
if ($action === 'delete') {
    if (!$userId) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
    $reviewId = (int)($_POST['review_id'] ?? 0);
    $check = $pdo->prepare("SELECT id FROM reviews WHERE id=? AND user_id=?");
    $check->execute([$reviewId, $userId]);
    if (!$check->fetch()) { echo json_encode(['success'=>false,'message'=>'Not allowed']); exit; }
    $pdo->prepare("DELETE FROM review_likes_index WHERE review_id=?")->execute([$reviewId]);
    $pdo->prepare("DELETE FROM review_replies WHERE review_id=?")->execute([$reviewId]);
    $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$reviewId]);
    echo json_encode(['success'=>true]);
    exit;
}

// ─── EDIT REVIEW ───────────────────────────────────────────
if ($action === 'edit') {
    if (!$userId) { echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
    $data = json_decode(file_get_contents('php://input'), true);
    $reviewId = (int)($data['review_id'] ?? 0);
    $content  = trim($data['content'] ?? '');
    $rating   = (int)($data['rating'] ?? 0);
    if (!$reviewId || !$content || $rating < 1 || $rating > 5) { echo json_encode(['success'=>false]); exit; }
    $check = $pdo->prepare("SELECT id FROM reviews WHERE id=? AND user_id=?");
    $check->execute([$reviewId, $userId]);
    if (!$check->fetch()) { echo json_encode(['success'=>false,'message'=>'Not allowed']); exit; }
    $pdo->prepare("UPDATE reviews SET content=?, rating=? WHERE id=?")->execute([$content, $rating, $reviewId]);
    echo json_encode(['success'=>true]);
    exit;
}

// ─── DELETE REPLY ──────────────────────────────────────────
if ($action === 'delete_reply') {
    if (!$userId) { echo json_encode(['success'=>false]); exit; }
    $replyId = (int)($_POST['reply_id'] ?? 0);
    $check = $pdo->prepare("SELECT id FROM review_replies WHERE id=? AND user_id=?");
    $check->execute([$replyId, $userId]);
    if (!$check->fetch()) { echo json_encode(['success'=>false,'message'=>'Not allowed']); exit; }
    $pdo->prepare("DELETE FROM review_replies WHERE id=?")->execute([$replyId]);
    echo json_encode(['success'=>true]);
    exit;
}

// ─── EDIT REPLY ────────────────────────────────────────────
if ($action === 'edit_reply') {
    if (!$userId) { echo json_encode(['success'=>false]); exit; }
    $data    = json_decode(file_get_contents('php://input'), true);
    $replyId = (int)($data['reply_id'] ?? 0);
    $content = trim($data['content'] ?? '');
    if (!$replyId || !$content) { echo json_encode(['success'=>false]); exit; }
    $check = $pdo->prepare("SELECT id FROM review_replies WHERE id=? AND user_id=?");
    $check->execute([$replyId, $userId]);
    if (!$check->fetch()) { echo json_encode(['success'=>false,'message'=>'Not allowed']); exit; }
    $pdo->prepare("UPDATE review_replies SET content=? WHERE id=?")->execute([$content, $replyId]);
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action']);