<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');
set_exception_handler(function($e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
set_error_handler(function($errno, $errstr) {
    echo json_encode(['error' => $errstr]);
    exit;
});
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$car_id = intval($_GET['car_id'] ?? 0);

if ($method === 'GET') {
    // جلب التعليقات الرئيسية فقط (parent_id IS NULL)
    $stmt = $pdo->prepare("
        SELECT r.id, r.rating, r.review_text, r.created_at,
        EXISTS(SELECT 1 FROM review_likes rl WHERE rl.review_id = r.id AND rl.user_id = ?) AS liked_by_me,
(SELECT COUNT(*) FROM review_likes rl2 WHERE rl2.review_id = r.id) AS likes,
               CONCAT(u.first_name,' ',u.last_name) AS user_name,
               u.id AS user_id,
               u.profile_photo,
               'var(--blue)' AS avatarColor
        FROM car_reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.car_id = ? AND (r.parent_id IS NULL OR r.parent_id = 0)
        ORDER BY r.created_at DESC
    ");
    $user_id_for_like = $_SESSION['user_id'] ?? 0;
$stmt->execute([$user_id_for_like, $car_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // جلب الردود لكل تعليق
    $replyStmt = $pdo->prepare("
        SELECT r.id, r.parent_id, r.rating, r.review_text, r.created_at,
               CONCAT(u.first_name,' ',u.last_name) AS user_name,
               u.id AS user_id,
               u.profile_photo,
               'var(--blue)' AS avatarColor
        FROM car_reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.car_id = ? AND r.parent_id IS NOT NULL AND r.parent_id > 0
        ORDER BY r.created_at ASC
    ");
    $replyStmt->execute([$car_id]);
    $allReplies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);

    // تجميع الردود داخل كل تعليق
    $repliesByParent = [];
    foreach ($allReplies as $rep) {
        $repliesByParent[$rep['parent_id']][] = $rep;
    }
    foreach ($reviews as &$review) {
        $review['replies'] = $repliesByParent[$review['id']] ?? [];
    }
    unset($review);

    // الإحصائيات (التعليقات الرئيسية فقط)
    $stats = $pdo->prepare("
        SELECT AVG(rating) as avg, COUNT(*) as cnt
        FROM car_reviews
        WHERE car_id = ? AND (parent_id IS NULL OR parent_id = 0) AND rating > 0
    ");
    $stats->execute([$car_id]);
    $s = $stats->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'reviews'    => $reviews,
        'avg_rating' => $s['avg'] ? round($s['avg'], 1) : 0,
        'count'      => (int)$s['cnt']
    ]);

} elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'not logged in']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['error' => 'invalid json']);
        exit;
    }
// Like handler
if (isset($data['action']) && $data['action'] === 'like') {
    $review_id = intval($data['review_id']);
    $user_id = $_SESSION['user_id'];
    $liked = $data['liked'];
    
    if ($liked) {
        $stmt = $pdo->prepare("REPLACE INTO review_likes (review_id, user_id) VALUES (?, ?)");
    } else {
        $stmt = $pdo->prepare("DELETE FROM review_likes WHERE review_id=? AND user_id=?");
    }
    $stmt->execute([$review_id, $user_id]);
    // إشعار لصاحب التعليق إذا like جديد
if ($liked) {
    $reviewOwner = $pdo->prepare("SELECT user_id FROM car_reviews WHERE id = ?");
    $reviewOwner->execute([$review_id]);
    $ownerRow = $reviewOwner->fetch(PDO::FETCH_ASSOC);
    
    if ($ownerRow && $ownerRow['user_id'] != $user_id) {
        $liker = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $liker->execute([$user_id]);
        $likerRow = $liker->fetch(PDO::FETCH_ASSOC);
        $likerName = trim(($likerRow['first_name'] ?? '') . ' ' . ($likerRow['last_name'] ?? ''));
        
        // جيب car_id من التعليق
        $reviewCar = $pdo->prepare("SELECT car_id FROM car_reviews WHERE id = ?");
        $reviewCar->execute([$review_id]);
        $carRow = $reviewCar->fetch(PDO::FETCH_ASSOC);
        
        $pdo->prepare("
            INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at)
            VALUES (?, ?, ?, ?, 'like', ?, NOW())
        ")->execute([
            $ownerRow['user_id'],
            $user_id,
            $likerName . ' liked your review',
            $likerName . ' liked your review',
            'listing.html?id=' . $carRow['car_id'] . '#review-' . $review_id
        ]);
    }
}
    // جيب العدد الجديد
    $count = $pdo->prepare("SELECT COUNT(*) FROM review_likes WHERE review_id=?");
    $count->execute([$review_id]);
    $newCount = $count->fetchColumn();
    
    echo json_encode(['success' => true, 'likes' => (int)$newCount]);
    exit;
}
    // parent_id للردود (null للتعليقات الرئيسية)
    $parent_id = null;
if (!empty($data['parent_id']) && intval($data['parent_id']) > 0) {
    $parent_id = intval($data['parent_id']);
}

    $stmt = $pdo->prepare("
        INSERT INTO car_reviews (car_id, user_id, parent_id, rating, review_text)
        VALUES (?, ?, ?, ?, ?)
    ");

    $ok = $stmt->execute([
        intval($data['car_id']),
        $_SESSION['user_id'],
        $parent_id,
        isset($data['rating']) && intval($data['rating']) > 0 ? intval($data['rating']) : null,
        trim($data['review_text'])
    ]);

    if ($ok) {
        $newId = $pdo->lastInsertId();
        // إشعار للوسيط إذا تعليق جديد (مو رد)
        if (!$parent_id) {
            // جيب agent_id من السيارة
            $carOwner = $pdo->prepare("SELECT agent_id, title FROM cars WHERE id = ?");
            $carOwner->execute([intval($data['car_id'])]);
            $carRow = $carOwner->fetch(PDO::FETCH_ASSOC);
            
            if ($carRow && $carRow['agent_id'] != $_SESSION['user_id']) {
                $commenter = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $commenter->execute([$_SESSION['user_id']]);
                $commenterRow = $commenter->fetch(PDO::FETCH_ASSOC);
                $commenterName = trim(($commenterRow['first_name'] ?? '') . ' ' . ($commenterRow['last_name'] ?? ''));
                
                $pdo->prepare("
                    INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at)
                    VALUES (?, ?, ?, ?, 'comment', ?, NOW())
                ")->execute([
                    $carRow['agent_id'],
                    $_SESSION['user_id'],
                    $commenterName . ' commented on your car',
                    trim($data['review_text']),
                    'listing.html?id=' . intval($data['car_id']). '#review-' . $newId
                ]);
            }
        }
        
        // إشعار لصاحب التعليق إذا كان رد
        if ($parent_id) {
            $parentReview = $pdo->prepare("SELECT user_id, review_text FROM car_reviews WHERE id = ?");
            $parentReview->execute([$parent_id]);
            $parentRow = $parentReview->fetch(PDO::FETCH_ASSOC);
            
            if ($parentRow && $parentRow['user_id'] != $_SESSION['user_id']) {
                $replier = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $replier->execute([$_SESSION['user_id']]);
                $replierRow = $replier->fetch(PDO::FETCH_ASSOC);
                $replierName = trim(($replierRow['first_name'] ?? '') . ' ' . ($replierRow['last_name'] ?? ''));
                
                $pdo->prepare("
                    INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at)
                    VALUES (?, ?, ?, ?, 'comment', ?, NOW())
                ")->execute([
                    $parentRow['user_id'],
                    $_SESSION['user_id'],
                    $replierName . ' replied to your comment',
                    trim($data['review_text']),
                    'listing.html?id=' . intval($data['car_id']) . '#review-' . $parent_id
                ]);
            }
        }
        $fetch = $pdo->prepare("
            SELECT r.id, r.parent_id, r.rating, r.review_text, r.created_at,
                   CONCAT(u.first_name,' ',u.last_name) AS user_name,
                   u.id AS user_id,
                   u.profile_photo,
               'var(--blue)' AS avatarColor
        FROM car_reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $fetch->execute([$newId]);
        $newReview = $fetch->fetch(PDO::FETCH_ASSOC);
        echo json_encode($newReview);
    } else {
        echo json_encode(['error' => 'db error']);
    }

} elseif ($method === 'PUT') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'not logged in']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $review_id = intval($data['review_id'] ?? 0);
    $new_text  = trim($data['review_text'] ?? '');

    // تأكد أن المستخدم هو صاحب التعليق
    $stmt = $pdo->prepare("UPDATE car_reviews SET review_text = ? WHERE id = ? AND user_id = ?");
    $ok = $stmt->execute([$new_text, $review_id, $_SESSION['user_id']]);

    echo json_encode(['success' => $ok]);

} elseif ($method === 'DELETE') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'not logged in']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $review_id = intval($data['review_id'] ?? 0);

    // تأكد أن المستخدم هو صاحب التعليق أو الرد
    $stmt = $pdo->prepare("DELETE FROM car_reviews WHERE id = ? AND user_id = ?");
    $ok = $stmt->execute([$review_id, $_SESSION['user_id']]);

    echo json_encode(['success' => $ok]);
}
?>