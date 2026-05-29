<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

$data     = json_decode(file_get_contents('php://input'), true);
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['error' => 'Missing fields']); exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) { echo json_encode(['error' => 'Email not found']); exit; }
if (!password_verify($password, $user['password_hash'])) { echo json_encode(['error' => 'Wrong password']); exit; }
if (!$user['is_verified']) { echo json_encode(['error' => 'Account not verified']); exit; }
if ($user['role'] === 'agent' && $user['status'] === 'pending') { echo json_encode(['error' => 'pending']); exit; }
if ($user['role'] === 'agent' && $user['status'] === 'blocked') {
    if (!empty($user['blocked_until'])) {
        $now = new DateTime();
        $until = new DateTime($user['blocked_until']);
        if ($now > $until) {
            // انتهت المدة → فك الحظر تلقائياً
            $pdo->prepare("UPDATE users SET status='active', blocked_until=NULL, block_reason=NULL WHERE id=?")
                ->execute([$user['id']]);
        } else {
            $remaining = $now->diff($until);
            $days = $remaining->days;
            $hours = $remaining->h;
            echo json_encode([
                'success'       => true,
                'blocked'       => true,
                'blocked_until' => $user['blocked_until'],
                'block_reason'  => $user['block_reason'],
                'remaining'     => $days.'d '.$hours.'h',
                'token'         => '',
                'user_id'       => $user['id'],
                'role'          => $user['role'],
                'status'        => 'blocked',
                'agent_status'  => 'blocked',
                'full_name'     => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'first_name'    => $user['first_name'] ?? '',
                'last_name'     => $user['last_name'] ?? '',
                'profile_photo' => !empty($user['profile_photo']) ? 'http://localhost/' . $user['profile_photo'] : null,
                'email'         => $user['email']
            ]);
            exit;
        }
    } else {
        echo json_encode(['error' => 'blocked']); exit;
    }
}
// Generate token
$token   = bin2hex(random_bytes(32)) . '_' . $user['id'] . '_' . time();
$expires = date('Y-m-d H:i:s', strtotime('+30 days'));
$pdo->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?,?,?)")
    ->execute([$user['id'], $token, $expires]);

// PHP session للـ agent_dashboard.php
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['email']   = $user['email'];
$_SESSION['name']    = $user['full_name'] ?? ($user['first_name'] ?? '');

$photoUrl = !empty($user['profile_photo'])
    ? 'http://localhost/' . $user['profile_photo']
    : null;
$agent_status = null;
if ($user['role'] === 'agent') {
    $agent_status = $user['status'] ?? 'pending';
}

echo json_encode([
    'success'        => true,
    'token'          => $token,
    'user_id'        => $user['id'],
    'role'           => $user['role'],
    'status'         => $user['status'] ?? '',
    'agent_status'   => $agent_status,
    'full_name'      => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
    'first_name'     => $user['first_name'] ?? '',
    'last_name'      => $user['last_name'] ?? '',
    'profile_photo'  => $photoUrl,
    'email'          =>$user['email']

]);