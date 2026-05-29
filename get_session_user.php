<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'not logged in']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, profile_photo FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'user not found']);
        exit;
    }

    echo json_encode([
        'id'            => $user['id'],
        'name'          => trim($user['first_name'] . ' ' . $user['last_name']),
        'email'         => $user['email'],
        'profile_photo' => $user['profile_photo'] ?? null,
        'avatarColor'   => 'var(--blue)'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}