<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    echo json_encode(['success' => false, 'msg' => 'Not authorized']);
    exit;
}

$agent_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'save';

// جلب السيارات
if ($action === 'get') {
    $stmt = $pdo->prepare("
        SELECT c.*, GROUP_CONCAT(p.photo_path ORDER BY p.id ASC SEPARATOR '|') AS photos
        FROM cars c
        LEFT JOIN car_photos p ON p.car_id = c.id
        WHERE c.agent_id = ?
        GROUP BY c.id
        ORDER BY c.id DESC
    ");
    $stmt->execute([$agent_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cars = array_map(function($c) {
        $imgs = $c['photos'] ? explode('|', $c['photos']) : [];
        return [
    'id'           => $c['id'],
    'title'        => $c['title'],
    'price'        => $c['price'],
    'status'       => $c['status'],
    'brand'        => $c['brand'],
    'year'         => $c['year'],
    'desc'         => $c['description'],
    'imgs'         => $imgs,
    'fuel_type'    => $c['fuel_type'] ?? '',
    'body_type'    => $c['body_type'] ?? '',
    'transmission' => $c['transmission'] ?? '',
    'drive_type'   => $c['drive_type'] ?? '',
    'seats'        => $c['seats'] ?? '',
    'mileage'      => $c['mileage'] ?? 0,
    'color_ext'    => $c['color_ext'] ?? '',
    'color_int'    => $c['color_int'] ?? '',
    'wilaya'       => $c['wilaya'] ?? '',
    'engine'       => $c['engine'] ?? '',
'power'        => $c['power'] ?? '',
'consumption'  => $c['consumption'] ?? '',
'delivery'     => $c['delivery'] ?? '',
'duty_free'    => $c['duty_free'] ?? 1,
'reservation_status'=> $c['reservation_status'] ?? 'available',
];
    }, $rows);
    echo json_encode(['success' => true, 'cars' => $cars]);
    exit;
}

// حذف سيارة
if ($action === 'delete') {
    $car_id = intval($_GET['car_id'] ?? 0);
    $check = $pdo->prepare("SELECT id FROM cars WHERE id = ? AND agent_id = ?");
    $check->execute([$car_id, $agent_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'msg' => 'Not found']);
        exit;
    }
    $pdo->prepare("DELETE FROM car_photos WHERE car_id = ?")->execute([$car_id]);
    $pdo->prepare("DELETE FROM cars WHERE id = ? AND agent_id = ?")->execute([$car_id, $agent_id]);
    echo json_encode(['success' => true]);
    exit;
}

// تغيير الحالة (sold/available)
if ($action === 'toggle_status') {
    $input = json_decode(file_get_contents('php://input'), true);
    $car_id = intval($input['car_id'] ?? 0);
    $status = $input['status'] ?? 'available';
    $pdo->prepare("UPDATE cars SET status = ? WHERE id = ? AND agent_id = ?")
        ->execute([$status, $car_id, $agent_id]);
    echo json_encode(['success' => true]);
    exit;
}

// تعديل سيارة
if ($action === 'update') {
    $car_id = intval($_POST['car_id'] ?? 0);
    $title  = $_POST['title'] ?? '';
    $price  = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? 'available';
    $brand  = $_POST['brand'] ?? '';
    $year   = $_POST['year'] ?? '';
    $desc   = $_POST['desc'] ?? '';
    if (!$title || !$price) {
        echo json_encode(['success' => false, 'msg' => 'Title and price required']);
        exit;
    }
    $fuel_type    = $_POST['fuel_type'] ?? '';
$body_type    = $_POST['body_type'] ?? '';
$transmission = $_POST['transmission'] ?? '';
$drive_type   = $_POST['drive_type'] ?? '';
$seats        = $_POST['seats'] ?? null;
$mileage      = $_POST['mileage'] ?? 0;
$color_ext    = $_POST['color_ext'] ?? '';
$color_int    = $_POST['color_int'] ?? '';
$wilaya       = $_POST['wilaya'] ?? '';
$engine      = $_POST['engine'] ?? '';
$power       = $_POST['power'] ?? '';
$consumption = $_POST['consumption'] ?? '';
$delivery    = $_POST['delivery'] ?? '';
$duty_free   = intval($_POST['duty_free'] ?? 1);
$specs_text    = $_POST['specs_text'] ?? '';
$contact_phone = trim($_POST['contact_phone'] ?? '');
if ($contact_phone) {
    $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?")->execute([$contact_phone, $agent_id]);
}
$pdo->prepare("UPDATE cars SET title=?, brand=?, year=?, price=?, status=?, description=?, fuel_type=?, body_type=?, transmission=?, drive_type=?, seats=?, mileage=?, color_ext=?, color_int=?, wilaya=?, engine=?, power=?, consumption=?, delivery=?, duty_free=?, specs_text=? WHERE id=? AND agent_id=?")
->execute([$title, $brand, $year, $price, $status, $desc, $fuel_type, $body_type, $transmission, $drive_type, $seats, $mileage, $color_ext, $color_int, $wilaya, $engine, $power, $consumption, $delivery, $duty_free, $specs_text, $car_id, $agent_id]);
    if (!empty($_FILES['photos']['name'][0])) {
        $uploadDir = 'uploads/cars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $pdo->prepare("DELETE FROM car_photos WHERE car_id = ?")->execute([$car_id]);
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
            $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
            $filename = $car_id . '_' . time() . '_' . $i . '.' . $ext;
            move_uploaded_file($tmp, $uploadDir . $filename);
            $pdo->prepare("INSERT INTO car_photos (car_id, photo_path) VALUES (?, ?)")
                ->execute([$car_id, $uploadDir . $filename]);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// إضافة سيارة جديدة
$title  = $_POST['title'] ?? '';
$price  = $_POST['price'] ?? 0;
$status = $_POST['status'] ?? 'available';
$brand  = $_POST['brand'] ?? '';
$year   = $_POST['year'] ?? '';
$desc   = $_POST['desc'] ?? '';

if (!$title || !$price) {
    echo json_encode(['success' => false, 'msg' => 'Title and price required']);
    exit;
}
$fuel_type    = $_POST['fuel_type'] ?? '';
$body_type    = $_POST['body_type'] ?? '';
$transmission = $_POST['transmission'] ?? '';
$drive_type   = $_POST['drive_type'] ?? '';
$seats        = $_POST['seats'] ?? null;
$mileage      = $_POST['mileage'] ?? 0;
$color_ext    = $_POST['color_ext'] ?? '';
$color_int    = $_POST['color_int'] ?? '';
$wilaya       = $_POST['wilaya'] ?? '';
$specs_text    = $_POST['specs_text'] ?? '';
$contact_phone = trim($_POST['contact_phone'] ?? '');
if ($contact_phone) {
    $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?")->execute([$contact_phone, $agent_id]);
}
$engine      = $_POST['engine'] ?? '';
$power       = $_POST['power'] ?? '';
$consumption = $_POST['consumption'] ?? '';
$delivery    = $_POST['delivery'] ?? '';
$duty_free   = intval($_POST['duty_free'] ?? 1);
$stmt = $pdo->prepare("INSERT INTO cars (agent_id, title, brand, year, price, status, description, fuel_type, body_type, transmission, drive_type, seats, mileage, color_ext, color_int, wilaya, engine, power, consumption, delivery, duty_free, specs_text) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$agent_id, $title, $brand, $year, $price, $status, $desc, $fuel_type, $body_type, $transmission, $drive_type, $seats, $mileage, $color_ext, $color_int, $wilaya, $engine, $power, $consumption, $delivery, $duty_free, $specs_text]);
$car_id = $pdo->lastInsertId();
// ── مطابقة الـ alerts وإرسال إشعارات للزبائن ──
$alertStmt = $pdo->query("SELECT * FROM alerts WHERE status = 'open'");
$openAlerts = $alertStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($openAlerts as $alert) {
    $match = true;

    // مطابقة الـ brand
    if (!empty($alert['brand']) && strtolower(trim($alert['brand'])) !== strtolower(trim($brand))) {
        $match = false;
    }
    // مطابقة الـ body_type
    if ($match && !empty($alert['body_type']) && strtolower(trim($alert['body_type'])) !== strtolower(trim($body_type))) {
        $match = false;
    }
    // مطابقة الـ fuel_type
    if ($match && !empty($alert['fuel_type']) && strtolower(trim($alert['fuel_type'])) !== strtolower(trim($fuel_type))) {
        $match = false;
    }
    // مطابقة السنة
    if ($match && !empty($alert['year_min']) && (int)$year < (int)$alert['year_min']) {
        $match = false;
    }
    if ($match && !empty($alert['year_max']) && (int)$year > (int)$alert['year_max']) {
        $match = false;
    }
    // مطابقة السعر (budget_max = أقصى مبلغ يقدر عليه الزبون)
    if ($match && !empty($alert['budget_max']) && (float)$price > (float)$alert['budget_max']) {
        $match = false;
    }
    if ($match && !empty($alert['budget_min']) && (float)$price < (float)$alert['budget_min']) {
        $match = false;
    }

    if (!$match) continue;

    // بناء رسالة الإشعار
    $carLink = 'listing.html?id=' . $car_id;
    $msgText = "A new car matching your alert has been listed: $title ($year) — " . number_format($price) . " DZD";

    // إشعار داخل الموقع دائماً
    $pdo->prepare("
        INSERT INTO notifications
            (user_id, sender_id, title, message, type, related_id, link, is_read, created_at)
        VALUES (?, ?, ?, ?, 'alert', ?, ?, 0, NOW())
    ")->execute([
        $alert['client_id'],
        $agent_id,
        '🚗 Car matching your alert is now available!',
        $msgText,
        $car_id,
        $carLink
    ]);

    // تحديث حالة الـ alert
    $pdo->prepare("UPDATE alerts SET status = 'responded' WHERE id = ?")
        ->execute([$alert['id']]);
}
// ── نهاية مطابقة الـ alerts ──
if (!empty($_FILES['photos']['name'][0])) {
    $uploadDir = 'uploads/cars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
        $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
        $filename = $car_id . '_' . time() . '_' . $i . '.' . $ext;
        move_uploaded_file($tmp, $uploadDir . $filename);
        $pdo->prepare("INSERT INTO car_photos (car_id, photo_path) VALUES (?, ?)")
            ->execute([$car_id, $uploadDir . $filename]);
    }
}

echo json_encode(['success' => true, 'car_id' => $car_id]);
?>