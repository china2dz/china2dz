<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$car_id        = intval($_POST['car_id'] ?? 0);
$client_id     = intval($_POST['client_id'] ?? 0);
$agent_id      = intval($_POST['agent_id'] ?? 0);
$conv_id       = intval($_POST['conversation_id'] ?? 0);
$first_name    = trim($_POST['first_name'] ?? '');
$last_name     = trim($_POST['last_name'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$pay_method    = $_POST['payment_method'] === 'golden_card' ? 'golden_card' : 'cheque';

if (!$client_id || !$agent_id || !$first_name || !$last_name || !$phone) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// رفع الملف
$payment_file = null;
if (!empty($_FILES['payment_file']['name'])) {
    $upload_dir = 'uploads/reservations/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $ext  = pathinfo($_FILES['payment_file']['name'], PATHINFO_EXTENSION);
    $name = 'res_' . time() . '_' . rand(100,999) . '.' . $ext;
    if (move_uploaded_file($_FILES['payment_file']['tmp_name'], $upload_dir . $name)) {
        $payment_file = $upload_dir . $name;
    }
}

if (!$payment_file) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload payment file']);
    exit;
}

// تحقق: السيارة متوفرة؟
// إذا ما تحدد car_id من الزبون، جيب أول سيارة متوفرة عند الوسيط
if (!$car_id) {
    $findCar = $pdo->prepare("SELECT id FROM cars WHERE agent_id = ? AND reservation_status != 'reserved' AND status = 'available' LIMIT 1");
    $findCar->execute([$agent_id]);
    $foundCar = $findCar->fetch();
    if (!$foundCar) {
        echo json_encode(['success' => false, 'message' => 'No available cars for this agent']);
        exit;
    }
    $car_id = $foundCar['id'];
} else {
    $check = $pdo->prepare("SELECT reservation_status FROM cars WHERE id = ?");
    $check->execute([$car_id]);
    $car = $check->fetch();
    if (!$car || $car['reservation_status'] === 'reserved') {
        echo json_encode(['success' => false, 'message' => 'Car is already reserved']);
        exit;
    }
}

// احفظ طلب الحجز
$stmt = $pdo->prepare("
    INSERT INTO car_reservations 
    (car_id, client_id, agent_id, conversation_id, first_name, last_name, phone, payment_method, payment_file, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([$car_id, $client_id, $agent_id, $conv_id, $first_name, $last_name, $phone, $pay_method, $payment_file]);
$reservation_id = $pdo->lastInsertId();
// جيب اسم السيارة
$carInfo = $pdo->prepare("SELECT title FROM cars WHERE id = ?");
$carInfo->execute([$car_id]);
$carRow = $carInfo->fetch();
$car_name = $carRow['title'] ?? 'a car';
// أرسل إشعار للوسيط
$notif = $pdo->prepare("
    INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
    VALUES (?, ?, ?, 'reservation', 0, NOW())
");
$notif->execute([
    $agent_id,
    '🔒 New Reservation Request',
    $first_name . ' ' . $last_name . ' wants to reserve: ' . $car_name . '. Review and confirm in Client Requests.'
]);
// أضيفي رسالة نوع reservation في الشات
if ($conv_id) {
    $resMsg = $pdo->prepare("
        INSERT INTO messages 
        (conversation_id, sender_id, message, message_type, reservation_id)
        VALUES (?, ?, ?, 'reservation', ?)
    ");
    $resMsg->execute([
        $conv_id, 
        $client_id, 
        'reservation_request', 
        $reservation_id
    ]);
    $pdo->prepare("
        UPDATE conversations SET last_message='Reservation Request', last_message_at=NOW() WHERE id=?
    ")->execute([$conv_id]);
}

echo json_encode(['success' => true, 'reservation_id' => $reservation_id]);