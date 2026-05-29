<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not logged in']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$client_id = $_SESSION['user_id'];
$agent_id  = intval($data['agent_id']);
$car_id    = intval($data['car_id']);
$car_name = $car_id ? $pdo->query("SELECT CONCAT(brand,' ',title,' ',year) FROM cars WHERE id=$car_id")->fetchColumn() : 'General Inquiry';
$message   = trim($data['message']);

if (!$message) { echo json_encode(['error' => 'empty message']); exit; }

// جيبي أو أنشئي conversation
$stmt = $pdo->prepare("
    SELECT id FROM conversations 
    WHERE client_id=? AND agent_id=?
    LIMIT 1
");
$stmt->execute([$client_id, $agent_id]);
$conv = $stmt->fetch(PDO::FETCH_ASSOC);

if ($conv) {
    $conv_id = $conv['id'];
} else {
    $ins = $pdo->prepare("
        INSERT INTO conversations (client_id, agent_id, car_id, car_name, last_message, last_message_at)
        VALUES (?,?,?,?,?,NOW())
    ");
    $ins->execute([$client_id, $agent_id, $car_id, $car_name, $message]);
    $conv_id = $pdo->lastInsertId();
}

// أضيفي الرسالة
$msg = $pdo->prepare("
    INSERT INTO messages (conversation_id, sender_id, message)
    VALUES (?,?,?)
");
$msg->execute([$conv_id, $client_id, $message]);

// حدّثي last_message في conversation
$pdo->prepare("
    UPDATE conversations SET last_message=?, last_message_at=NOW() WHERE id=?
")->execute([$message, $conv_id]);

// إشعار للـ agent
// جيبي اسم وصورة الزبون
$sender = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE id=?");
$sender->execute([$client_id]);
$senderData = $sender->fetch(PDO::FETCH_ASSOC);
$senderName = trim(($senderData['first_name']??'') . ' ' . ($senderData['last_name']??''));

$pdo->prepare("
    INSERT INTO notifications (user_id, from_user_id, title, message, type)
    VALUES (?, ?, ?, ?, 'message')
")->execute([
    $agent_id,
    $client_id,
    'New Message from ' . $senderName,
    'You have a new message about ' . $car_name
]);

echo json_encode(['success' => true, 'conv_id' => $conv_id]);