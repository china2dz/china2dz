<?php
require_once 'config.php';

$stmt = $pdo->prepare("
    SELECT cr.id, cr.car_id, cr.client_id, cr.agent_id,
           cr.reserved_until, cr.visited,
           c.title AS car_title,
           CONCAT(u.first_name, ' ', u.last_name) AS client_name
    FROM car_reservations cr
    JOIN cars c ON c.id = cr.car_id
    JOIN users u ON u.id = cr.client_id
    WHERE cr.status = 'accepted'
      AND cr.visited = 0
      AND cr.reserved_until IS NOT NULL
      AND NOW() >= cr.reserved_until
      AND (cr.reminder_sent IS NULL OR cr.reminder_sent = 0)
");
$stmt->execute();
$expired = $stmt->fetchAll();

foreach ($expired as $r) {
    $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at)
        VALUES (?, ?, ?, 'warn', ?, 0, NOW())
    ")->execute([
        $r['agent_id'],
        'Did the client visit? — ' . $r['car_title'],
        'The 4-day window has passed for ' . $r['client_name'] . '. Please update the visit status.',
        'agent_dashboard.php'
    ]);

    $pdo->prepare("
        UPDATE car_reservations SET reminder_sent = 1 WHERE id = ?
    ")->execute([$r['id']]);
    // رجّع السيارة لـ available إذا انتهت المدة
$pdo->prepare("
    UPDATE cars SET reservation_status = 'available', reserved_until = NULL 
    WHERE id = ?
")->execute([$r['car_id']]);
}

echo json_encode(['checked' => count($expired)]);