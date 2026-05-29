<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');
require_once 'config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['error' => 'missing id']); exit; }

$stmt = $pdo->prepare("
    SELECT c.*, 
           u.id AS agent_user_id,
           CONCAT(u.first_name,' ',u.last_name) AS agent_name,
           u.phone AS agent_phone,
           u.phone AS whatsapp
    FROM cars c
    JOIN users u ON c.agent_id = u.id
    LEFT JOIN agent_profiles ap ON ap.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) { echo json_encode(['error' => 'not found']); exit; }

$photos = $pdo->prepare("SELECT photo_path FROM car_photos WHERE car_id = ? ORDER BY id ASC");
$photos->execute([$id]);
$rawImages = $photos->fetchAll(PDO::FETCH_COLUMN);
$car['images'] = array_map(function($path) {
    return 'http://localhost/' . ltrim($path, '/');
}, $rawImages);

// بناء البيانات اللي بتحتاجها الـ JS
$car['brandFull']    = $car['brand']        ?? '';
$car['color']        = $car['color_ext']    ?? '';
$car['colorHex']     = '#888888';
$car['fuelType']     = $car['fuel_type']    ?? '';
$car['priceCurrency']= 'DZD';
$car['dutyFree']     = !empty($car['duty_free']);
$car['badge']        = $car['status']       ?? '';
$car['model']        = $car['title']        ?? '';

$car['specs'] = [
    'brand'        => $car['brand']        ?? '—',
    'model'        => $car['title']        ?? '—',
    'year'         => $car['year']         ?? '—',
    'engine'       => $car['engine']       ?? '_',
    'power'        => $car['power']        ?? '_',
    'transmission' => $car['transmission'] ?? '—',
    'fuel'         => $car['fuel_type']    ?? '—',
    'consumption'  => $car['consumption'] ?? '_',
    'color'        => $car['color_ext']    ?? '—',
    'wilaya'       => $car['wilaya']       ?? '—',
    'mileage'      => $car['mileage']      ?? '—',
    'delivery'     => $car['delivery']     ?? '_',
];
$car['agent_phone'] = $car['agent_phone'] ?? '';
echo json_encode($car);