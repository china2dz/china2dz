<?php
require 'config.php';
header('Content-Type: application/json');

// ── GET (جلب الإشعارات) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action  = $_GET['action']  ?? '';
    $user_id = (int)($_GET['user_id'] ?? 0);

    if (!$user_id) {
        echo json_encode(['error' => 'Missing user_id']);
        exit;
    }

    if ($action === 'get') {
        $stmt = $pdo->prepare("
    SELECT n.id, n.user_id, n.title, n.message, n.type, n.related_id, n.link, n.is_read, n.created_at,
           n.sender_id,
           CONCAT(u.first_name, ' ', u.last_name) AS sender_name,
           u.profile_photo AS sender_photo
    FROM notifications n
    LEFT JOIN users u ON u.id = n.sender_id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 100
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($notifications as &$n) {
    $n['is_read']    = (int)$n['is_read'];
    $n['related_id'] = $n['related_id'] ? (int)$n['related_id'] : null;
    $n['sender_id']  = $n['sender_id']  ? (int)$n['sender_id']  : null;
    // Build photo URL
    if ($n['sender_photo']) {
    // إذا مو فيها http يعني مو URL كامل
    if (strpos($n['sender_photo'], 'http') !== 0) {
        $photo = ltrim($n['sender_photo'], '/');
        $n['sender_photo'] = 'http://localhost/' . $photo;
    }
} else {
    $n['sender_photo'] = null;
}
}

        echo json_encode(['success' => true, 'notifications' => $notifications]);
        exit;
    }

    if ($action === 'count_unread') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $count = (int)$stmt->fetchColumn();
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }

    echo json_encode(['error' => 'Unknown action']);
    exit;
}

// ── POST (إجراءات) ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data    = json_decode(file_get_contents('php://input'), true);
    $action  = $data['action']  ?? '';
    $user_id = (int)($data['user_id'] ?? $data['client_id'] ?? 0);

    if (!$user_id) {
        echo json_encode(['error' => 'Missing user_id']);
        exit;
    }

    // ── تعليم إشعار واحد كمقروء
    if ($action === 'mark_read') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing id']); exit; }

        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    // ── تعليم الكل كمقروء
    if ($action === 'mark_all_read') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    // ── حذف إشعار واحد
    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing id']); exit; }

        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    // ── حذف الكل
    if ($action === 'delete_all') {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->execute([$user_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    // ── إضافة إشعار جديد (للاستخدام من باقي الصفحات)
    if ($action === 'add') {
        $to_user_id = (int)($data['to_user_id'] ?? 0);
        $title      = trim($data['title']      ?? '');
        $message    = trim($data['message']    ?? '');
        $type       = $data['type']       ?? 'info'; // info, success, warning, offer, message, like, comment
        $related_id = (int)($data['related_id'] ?? 0) ?: null;
        $link       = trim($data['link']       ?? '') ?: null;

        if (!$to_user_id || !$title) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $allowed_types = ['info','success','warning','offer','message','like','comment'];
        if (!in_array($type, $allowed_types)) $type = 'info';

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, related_id, link, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$to_user_id, $title, $message, $type, $related_id, $link]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        exit;
    }
// ── إنشاء alert جديد
    if ($action === 'create_alert') {
        $client_id    = (int)($data['client_id'] ?? 0);
        $client_name  = trim($data['client_name']  ?? '');
        $client_email = trim($data['client_email'] ?? '');
        $client_phone = trim($data['client_phone'] ?? '');
        $brand        = trim($data['brand']        ?? '');
        $body_type    = trim($data['body_type']    ?? '');
        $fuel_type    = trim($data['fuel_type']    ?? '');
        $year_min     = !empty($data['year_min'])   ? (int)$data['year_min']    : null;
        $year_max     = !empty($data['year_max'])   ? (int)$data['year_max']    : null;
        $budget_min   = !empty($data['budget_min']) ? (float)$data['budget_min']: null;
        $budget_max   = !empty($data['budget_max']) ? (float)$data['budget_max']: null;
        $description  = trim($data['description']  ?? '');
        $frequency    = in_array($data['frequency'] ?? '', ['instant','daily','weekly'])
                        ? $data['frequency'] : 'instant';

        if (!$client_id) {
            echo json_encode(['error' => 'Missing client_id']); exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO alerts
                (client_id, client_name, client_email, client_phone,
                 brand, body_type, fuel_type,
                 year_min, year_max, budget_min, budget_max,
                 description, frequency, status, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'open', NOW())
        ");
        $stmt->execute([
            $client_id, $client_name, $client_email, $client_phone,
            $brand, $body_type, $fuel_type,
            $year_min, $year_max, $budget_min, $budget_max,
            $description, $frequency
        ]);
        $alert_id = $pdo->lastInsertId();

        // بناء نص التفاصيل
        $details = [];
        if ($brand)      $details[] = "Brand: $brand";
        if ($body_type)  $details[] = "Body: $body_type";
        if ($fuel_type)  $details[] = "Fuel: $fuel_type";
        if ($year_min)   $details[] = "Year from: $year_min";
        if ($year_max)   $details[] = "Year to: $year_max";
        if ($budget_max) $details[] = "Max budget: " . number_format($budget_max) . " DZD";
        if ($description) $details[] = "Search: $description";
$detailsText = implode(' | ', $details) ?: 'Any car';

        // أرسل إشعار لكل الـ agents
        $agents = $pdo->query("
            SELECT id FROM users WHERE role = 'agent' AND status = 'approved'
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($agents as $agent) {
            $pdo->prepare("
                INSERT INTO notifications
                    (user_id, sender_id, title, message, type, link, is_read, created_at)
                VALUES (?, ?, ?, ?, 'offer', ?, 0, NOW())
            ")->execute([
                $agent['id'],
                $client_id,
                '🔔 New Car Alert from ' . $client_name,
                $detailsText,
                'agent_dashboard.php?tab=alerts'
            ]);
        }

        echo json_encode(['success' => true, 'alert_id' => $alert_id]);
        exit;
    }
    echo json_encode(['error' => 'Unknown action']);
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
?>