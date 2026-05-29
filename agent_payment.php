<?php
session_start();
file_put_contents('C:/xampp/htdocs/china2dz/test_pay.txt', 
    "SESSION: " . print_r($_SESSION, true) . 
    "\nPOST: " . print_r($_POST, true) . 
    "\nFILES: " . print_r($_FILES, true)
);
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.html'); exit;
}

header('Content-Type: application/json');

$method    = trim($_POST['payment_method'] ?? '');
$reference = trim($_POST['payment_reference'] ?? '');

if (!$method || !$reference) {
    echo json_encode(['error' => 'Please fill all fields']); exit;
}

$proof_file = '';
if (!empty($_FILES['proof_file']['tmp_name'])) {
    $upload_dir = 'uploads/payments/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $ext = pathinfo($_FILES['proof_file']['name'], PATHINFO_EXTENSION);
    $proof_file = $upload_dir . 'pay_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['proof_file']['tmp_name'], $proof_file);
}

$stmt = $pdo->prepare("
    INSERT INTO subscriptions (user_id, amount, payment_method, payment_reference, proof_file, status)
    VALUES (?, 1500, ?, ?, ?, 'pending')
");
$stmt->execute([$_SESSION['user_id'], $method, $reference, $proof_file]);
$adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
$agentName = $pdo->query("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=".$_SESSION['user_id'])->fetchColumn();
$pdo->prepare("INSERT INTO notifications (user_id, sender_id, title, message, type, created_at) VALUES (?, ?, 'New Subscription Request', ?, 'info', NOW())")
    ->execute([$adminId, $_SESSION['user_id'], $agentName.' submitted a payment proof.']);
echo json_encode(['success' => true]);
?>