<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }

$uid = $_SESSION['user_id'];
$plan = $_POST['plan'] ?? 'Pro';
$amount = $_POST['amount'] ?? 0;
$method = $_POST['payment_method'] ?? '';
$ref = $_POST['payment_reference'] ?? '';

$proofPath = '';
if (!empty($_FILES['proof']['name'])) {
    $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
    $filename = 'pay_'.$uid.'_'.time().'.'.$ext;
    $proofPath = 'uploads/payments/'.$filename;
    move_uploaded_file($_FILES['proof']['tmp_name'], $proofPath);
}

// 1. احفظ الـ subscription
$stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan, amount, payment_method, payment_reference, proof_file, status) VALUES (?,?,?,?,?,?,'pending')");
$stmt->execute([$uid, $plan, $amount, $method, $ref, $proofPath]);
$sub_id = $pdo->lastInsertId();

// 2. ✅ حدّث الـ session فورًا
$_SESSION['sub_status'] = 'pending';
$_SESSION['sub_plan'] = $plan;

// 3. ✅ أرسل إشعار للأدمين
$agent_name = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
$notif_title = "طلب اشتراك جديد";
$notif_msg = "الوكيل $agent_name طلب اشتراك $plan بمبلغ $amount DZD";

// جيب أول أدمين
$admin = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
if ($admin) {
    $stmt2 = $pdo->prepare("
        INSERT INTO notifications (user_id, sender_id, title, message, type, related_id)
        VALUES (?, ?, ?, ?, 'info', ?)
    ");
    $stmt2->execute([$admin['id'], $uid, $notif_title, $notif_msg, $sub_id]);
}

echo json_encode(['success'=>true]);
?>