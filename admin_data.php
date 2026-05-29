<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
     echo json_encode(['error' => 'unauthorized']);
    exit;
}

header('Content-Type: application/json');

// جلب الوسطاء
$agents = $pdo->query("
    SELECT u.id, u.email, u.status, u.created_at,
           u.first_name, u.last_name, u.phone,
           ap.national_id, ap.id_card_name, 
           ap.company_name, ap.rc_number, ap.rc_owner_name
    FROM users u
    LEFT JOIN agent_profiles ap ON ap.user_id = u.id
    WHERE u.role = 'agent'
    ORDER BY u.created_at DESC
")->fetchAll();

// جلب طلبات الدفع
$payments = $pdo->query("
    SELECT s.*, u.email, u.first_name, u.last_name
    FROM subscriptions s
    JOIN users u ON u.id = s.user_id
    ORDER BY s.created_at DESC
")->fetchAll();

echo json_encode([
    'agents'   => $agents,
    'payments' => $payments
]);
?>