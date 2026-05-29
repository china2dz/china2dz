<?php
require 'config.php';
header('Content-Type: application/json');

$data    = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? '';
$code    = $data['code'] ?? '';
file_put_contents('debug.txt', "user_id=$user_id, code=$code\n", FILE_APPEND);
error_log("user_id:$user_id,code:$code");

if (!$user_id || !$code) {
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE user_id = ? AND code = ? AND used = 0 ");
$stmt->execute([$user_id,$code]);
$otp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$otp) {
    echo json_encode(['error' => 'Invalid or expired code']);
    exit;
}

// تفعيل الحساب
$stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
$stmt->execute([$user_id]);

// تعليم الكود كمستخدم
$stmt = $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
$stmt->execute([$otp['id']]);

$stmt = $pdo->prepare("SELECT role, status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
session_start();
$_SESSION['user_id'] = $user_id;
$_SESSION['role'] = $user['role'];

// إنشاء token
$token = bin2hex(random_bytes(32)) . '_' . $user_id . '_' . time();
$expires = date('Y-m-d H:i:s', strtotime('+30 days'));
try {
    $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?,?,?)")
        ->execute([$user_id, $token, $expires]);
} catch(Exception $e) {}

$stmt = $pdo->prepare("SELECT id, role, status, first_name, last_name, profile_photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'role'    => $user['role'],
    'status'  => $user['status'],
    'token'   => $token,
    'user_id' => $user_id,
    'full_name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
    'profile_photo' => $user['profile_photo'] ?? null
]);
?>