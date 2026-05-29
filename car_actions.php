<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$agentId = $_SESSION['user_id'];

/* ══ GET car data ══ */
if ($action === 'get') {
    $carId = intval($_GET['car_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND agent_id = ?");
    $stmt->execute([$carId, $agentId]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$car) { echo json_encode(['success' => false, 'message' => 'Not found']); exit; }
    $photos = $pdo->prepare("SELECT id, photo_path as path FROM car_photos WHERE car_id = ?");
$photos->execute([$carId]);
$car['photos'] = $photos->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'car' => $car]);
    exit;
}
    /* == DELETE PHOTO == */
if ($action === 'delete_photo') {
    $photoId = (int)$_POST['photo_id'];
    $stmt = $pdo->prepare("
        SELECT p.photo_path FROM car_photos p
        JOIN cars c ON c.id = p.car_id
        WHERE p.id = ? AND c.agent_id = ?
    ");
    $stmt->execute([$photoId, $agentId]);
    $photo = $stmt->fetch();
    if (!$photo) { echo json_encode(['success'=>false,'message'=>'Not found']); exit; }
    $path = $_SERVER['DOCUMENT_ROOT'] . '/' . $photo['photo_path'];
    if (file_exists($path)) unlink($path);
    $pdo->prepare("DELETE FROM car_photos WHERE id = ?")->execute([$photoId]);
    echo json_encode(['success'=>true]);
    exit;
}


/* ══ DELETE car ══ */
if ($action === 'delete') {
    $carId = intval($_POST['car_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id FROM cars WHERE id = ? AND agent_id = ?");
    $stmt->execute([$carId, $agentId]);
    if (!$stmt->fetch()) { echo json_encode(['success' => false, 'message' => 'Not found']); exit; }
    $pdo->prepare("DELETE FROM car_photos WHERE car_id = ?")->execute([$carId]);
    $pdo->prepare("DELETE FROM cars WHERE id = ? AND agent_id = ?")->execute([$carId, $agentId]);
    echo json_encode(['success' => true]);
    exit;
}

/* ══ EDIT car ══ */
if ($action === 'edit') {
    $carId = intval($_POST['car_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id FROM cars WHERE id = ? AND agent_id = ?");
    $stmt->execute([$carId, $agentId]);
    if (!$stmt->fetch()) { echo json_encode(['success' => false, 'message' => 'Not found']); exit; }

    $title = trim($_POST['title'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $year  = intval($_POST['year']  ?? 0);
    $desc  = trim($_POST['description'] ?? '');

    $engine       = trim($_POST['engine'] ?? '');
$power        = trim($_POST['power'] ?? '');
$consumption  = trim($_POST['consumption'] ?? '');
$delivery     = trim($_POST['delivery'] ?? '');
$specs_text   = trim($_POST['specs_text'] ?? '');
$transmission = trim($_POST['transmission'] ?? '');
$fuel_type    = trim($_POST['fuel_type'] ?? '');
$contact_phone= trim($_POST['contact_phone'] ?? '');

$pdo->prepare("UPDATE cars SET 
    title=?, price=?, year=?, description=?,
    engine=?, power=?, consumption=?, delivery=?,
    specs_text=?, transmission=?, fuel_type=?, contact_phone=?
    WHERE id=? AND agent_id=?")
    ->execute([
        $title, $price, $year, $desc,
        $engine, $power, $consumption, $delivery,
        $specs_text, $transmission, $fuel_type, $contact_phone,
        $carId, $agentId
    ]);

    /* Upload new photos */
    if (!empty($_FILES['photos']['name'][0])) {
        $uploadDir = 'uploads/cars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
            if (!is_uploaded_file($tmp)) continue;
            $ext  = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
            $name = 'car_' . $carId . '_' . time() . '_' . $i . '.' . $ext;
            $dest = $uploadDir . $name;
            if (move_uploaded_file($tmp, $dest)) {
                $pdo->prepare("INSERT INTO car_photos (car_id, photo_path) VALUES (?, ?)")
                    ->execute([$carId, $dest]);
            }
        }
    }

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);