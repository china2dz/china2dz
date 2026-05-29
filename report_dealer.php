<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

$data     = json_decode(file_get_contents('php://input'), true);
$agent_id = (int)($data['agent_id'] ?? 0);
$reason   = trim($data['reason']  ?? '');
$details  = trim($data['details'] ?? '');

if (!$agent_id || !$reason) {
    echo json_encode(['error' => 'Invalid data']); exit;
}

$reporter_id   = $_SESSION['user_id'] ?? null;
$reporter_name = '';

if ($reporter_id) {
    $rn = $pdo->prepare("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=?");
    $rn->execute([$reporter_id]);
    $reporter_name = $rn->fetchColumn() ?: '';
}

try {
    // حفظ التبليغ
    $pdo->prepare("INSERT INTO agent_reports 
        (agent_id, reporter_id, reporter_name, reason, details, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'open', NOW())")
        ->execute([$agent_id, $reporter_id, $reporter_name, $reason, $details]);

    // اسم الـ agent
    $agentStmt = $pdo->prepare("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=?");
    $agentStmt->execute([$agent_id]);
    $agentName = $agentStmt->fetchColumn() ?: 'Unknown';

    // إشعار لكل الأدمين
    $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($admins as $admin_id) {
        $pdo->prepare("INSERT INTO notifications 
    (user_id, sender_id, title, message, type, is_read, created_at)
    VALUES (?, ?, ?, ?, 'warning', 0, NOW())")
    ->execute([
        $admin_id,
        $reporter_id ?? 0,
        'New Dealer Report',
        "Report against: $agentName — Reason: $reason"
    ]);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>