<?php
require 'config.php';
header('Content-Type: application/json');

// ── GET ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action  = $_GET['action']  ?? '';
    $user_id = (int)($_GET['user_id'] ?? 0);

    if (!$user_id) { echo json_encode(['error' => 'Missing user_id']); exit; }

    // جلب كل السيارات المحفوظة
    if ($action === 'get') {
        $stmt = $pdo->prepare("
    SELECT f.*,
           c.title, c.brand, c.year, c.price, c.fuel_type, c.wilaya, c.transmission,
           c.body_type, c.mileage, c.seats, c.description,
           u.phone, u.first_name, u.last_name,
           ap.company_name,
           c.agent_id,
           GROUP_CONCAT(p.photo_path ORDER BY p.id ASC SEPARATOR '|') AS photos
    FROM favorites f
    LEFT JOIN cars c ON c.id = f.car_id
    LEFT JOIN users u ON u.id = c.agent_id
    LEFT JOIN agent_profiles ap ON ap.user_id = c.agent_id
    LEFT JOIN car_photos p ON p.car_id = f.car_id
    WHERE f.user_id = ?
    GROUP BY f.id
    ORDER BY f.saved_at DESC
");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'favorites' => $favorites]);
        exit;
    }

    // التحقق إذا سيارة محفوظة
    if ($action === 'check') {
        $car_id = $_GET['car_id'] ?? '';
        if (!$car_id) { echo json_encode(['saved' => false]); exit; }
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND car_id = ?");
        $stmt->execute([$user_id, $car_id]);
        echo json_encode(['saved' => (bool)$stmt->fetch()]);
        exit;
    }

    echo json_encode(['error' => 'Unknown action']); exit;
}

// ── POST ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data    = json_decode(file_get_contents('php://input'), true);
    $action  = $data['action']  ?? '';
    $user_id = (int)($data['user_id'] ?? 0);

    if (!$user_id) { echo json_encode(['error' => 'Missing user_id']); exit; }

    // ── حفظ سيارة
    if ($action === 'save') {
        $car_id    = $data['car_id']    ?? '';
        $car_name  = $data['car_name']  ?? '';
        $car_image = $data['car_image'] ?? '';
        $car_price = $data['car_price'] ?? '';
        $car_link  = $data['car_link']  ?? '';

        if (!$car_id) { echo json_encode(['error' => 'Missing car_id']); exit; }

        // تحقق إذا موجودة مسبقاً
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND car_id = ?");
        $stmt->execute([$user_id, $car_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => true, 'already_saved' => true]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, car_id, car_name, car_image, car_price, car_link, saved_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $car_id, $car_name, $car_image, $car_price, $car_link]);
        echo json_encode(['success' => true, 'saved' => true]);
        exit;
    }

    // ── حذف سيارة
    if ($action === 'remove') {
        $car_id = $data['car_id'] ?? '';
        if (!$car_id) { echo json_encode(['error' => 'Missing car_id']); exit; }

        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND car_id = ?");
        $stmt->execute([$user_id, $car_id]);
        echo json_encode(['success' => true, 'removed' => true]);
        exit;
    }

    // ── حذف بالـ id
    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing id']); exit; }

        $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ── حذف الكل
    if ($action === 'clear') {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['error' => 'Unknown action']); exit;
}

echo json_encode(['error' => 'Invalid request']);
?>