<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'china2dz');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('BASE_URL', 'http://localhost/');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    return $pdo;
}

function respond($code, $message, $data = null) {
    http_response_code($code);
    $res = ['success' => $code < 400, 'message' => $message];
    if ($data !== null) $res['data'] = $data;
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

function getInput() {
    $json = file_get_contents('php://input');
    return $json ? json_decode($json, true) : $_POST;
}

function generateToken($userId) {
    return bin2hex(random_bytes(32)) . '_' . $userId . '_' . time();
}

function getUserFromToken() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (!$auth || strpos($auth, 'Bearer ') !== 0) return null;
    $token = substr($auth, 7);
    $db = getDB();
    $stmt = $db->prepare("SELECT u.* FROM users u JOIN user_sessions s ON u.id = s.user_id WHERE s.session_token = ? AND s.expires_at > NOW()");
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function requireAuth() {
    $user = getUserFromToken();
    if (!$user) respond(401, 'Authentication required');
    return $user;
}
function getPhotoUrl($photo) {
    if (!$photo) return null;
    if (strpos($photo, 'uploads/') !== 0) $photo = 'uploads/' . $photo;
    return 'http://localhost/' . $photo;
}
$action = $_GET['action'] ?? '';
switch ($action) {

case 'register':
    $data = getInput();
    $name = trim($data['full_name'] ?? '');
$firstName = trim($data['first_name'] ?? '');
$lastName  = trim($data['last_name'] ?? '');
if (!$name && $firstName) $name = trim("$firstName $lastName");
    $email = trim(strtolower($data['email'] ?? ''));
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $role = in_array($data['role'] ?? '', ['client', 'agent']) ? $data['role'] : 'client';
    if (!$name || !$email || !$phone || !$password) respond(400, 'All fields are required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(400, 'Invalid email address');
    if (!preg_match('/^0[5-7]\d{8}$/', $phone)) respond(400, 'Invalid phone number');
    if (strlen($password) < 8) respond(400, 'Password must be at least 8 characters');
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) respond(409, 'Email or phone already registered');
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $otp = rand(100000, 999999);
    $otpExp = date('Y-m-d H:i:s', strtotime('+10 minutes'));
$stmt = $db->prepare("INSERT INTO users (full_name, first_name, last_name, email, phone, password, role, otp_code, otp_expires_at) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->execute([$name, $firstName, $lastName, $email, $phone, $hash, $role, $otp, $otpExp]);
    $userId = $db->lastInsertId();
    if ($role === 'agent') {
        $db->prepare("INSERT INTO agent_profiles (user_id, agency_name) VALUES (?,?)")->execute([$userId, $data['agency_name'] ?? '']);
    }
    $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")->execute([$userId, 'Welcome to China2DZ!', 'Your account has been created successfully!', 'success']);
    respond(201, 'Account created', ['otp' => $otp, 'user_id' => $userId]);

case 'verify_otp':
    $data = getInput();
    $userId = intval($data['user_id'] ?? 0);
    $otp = trim($data['otp'] ?? '');
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND otp_code = ? AND otp_expires_at > NOW()");
    $stmt->execute([$userId, $otp]);
    if (!$stmt->fetch()) respond(400, 'Invalid or expired OTP');
    $db->prepare("UPDATE users SET is_verified = 1, otp_code = NULL WHERE id = ?")->execute([$userId]);
    respond(200, 'Phone verified successfully');

case 'login':
    $data = getInput();
    $identifier = trim($data['identifier'] ?? '');
    $password = $data['password'] ?? '';
    if (!$identifier || !$password) respond(400, 'Please enter your credentials');
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) respond(401, 'Invalid credentials');
    $token = generateToken($user['id']);
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    $db->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?,?,?)")->execute([$user['id'], $token, $expires]);
    unset($user['password'], $user['otp_code']);
    $user['profile_photo_url'] = getPhotoUrl($user['profile_photo'] ?? '');
$user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if ($user['role'] === 'agent') {
    $sub = $db->prepare("SELECT status FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $sub->execute([$user['id']]);
    $subscription = $sub->fetch();
    $user['agent_status'] = $user['status'] ?? 'pending';
    $user['subscription_status'] = $subscription['status'] ?? 'none';
}
respond(200, 'Login successful', ['token' => $token, 'user' => $user]);

case 'logout':
    $user = requireAuth();
    $headers = getallheaders();
    $token = substr($headers['Authorization'] ?? '', 7);
    getDB()->prepare("DELETE FROM user_sessions WHERE session_token = ?")->execute([$token]);
    respond(200, 'Logged out');

case 'get_profile':
    $user = requireAuth();
    $user['profile_photo_url'] = getPhotoUrl($user['profile_photo'] ?? '');
    $user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    unset($user['password'], $user['otp_code']);

    // إضافة status الاشتراك للـ agent
    if ($user['role'] === 'agent') {
        $db = getDB();
        $sub = $db->prepare("SELECT status FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $sub->execute([$user['id']]);
        $subscription = $sub->fetch();
        $user['agent_status']       = $user['status'] ?? 'pending';
        $user['subscription_status'] = $subscription['status'] ?? 'none';
    }

    respond(200, 'ok', $user);

case 'update_profile':
    $user = requireAuth();
    $data = getInput();
    $db = getDB();
    $fields = []; $values = [];
    if (isset($data['full_name']) && trim($data['full_name']) !== '') {
        $parts = explode(' ', trim($data['full_name']), 2);
        $fields[] = "first_name = ?"; $values[] = $parts[0];
        $fields[] = "last_name = ?";  $values[] = $parts[1] ?? '';
        $fields[] = "full_name = ?";  $values[] = trim($data['full_name']);
    }
    foreach (['bio', 'wilaya'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f = ?"; $values[] = trim($data[$f]); }
    }
    // sync الولاية في agent_profiles إذا كان وسيط
if ($user['role'] === 'agent' && isset($data['wilaya'])) {
    $db->prepare("UPDATE agent_profiles SET wilaya = ? WHERE user_id = ?")
       ->execute([trim($data['wilaya']), $user['id']]);
}
    if (empty($fields)) respond(400, 'Nothing to update');
    $values[] = $user['id'];
    $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?")->execute($values);
    respond(200, 'Profile updated');

case 'upload_photo':
    $user = requireAuth();
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) respond(400, 'Invalid file');
    $file = $_FILES['photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) respond(400, 'Only JPG/PNG/WEBP allowed');
    if ($file['size'] > 5 * 1024 * 1024) respond(400, 'File exceeds 5MB');
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'uploads/photos/' . uniqid() . '.' . $ext;
if (!is_dir(__DIR__ . '/uploads/photos')) mkdir(__DIR__ . '/uploads/photos', 0777, true);
move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $filename);
getDB()->prepare("UPDATE users SET profile_photo = ? WHERE id = ?")->execute([$filename, $user['id']]);
respond(200, 'Photo uploaded', ['url' => BASE_URL . $filename]);

case 'get_favorites':
    $user = requireAuth();
    $stmt = getDB()->prepare("SELECT * FROM favorites WHERE user_id = ? ORDER BY saved_at DESC");
    $stmt->execute([$user['id']]);
    respond(200, 'ok', $stmt->fetchAll());

case 'add_favorite':
    $user = requireAuth();
    $data = getInput();
    if (!($data['car_id'] ?? '')) respond(400, 'car_id is required');
    $db = getDB();
    $db->prepare("INSERT IGNORE INTO favorites (user_id, car_id, car_name, car_image, car_price) VALUES (?,?,?,?,?)")->execute([$user['id'], $data['car_id'], $data['car_name'] ?? '', $data['car_image'] ?? '', $data['car_price'] ?? '']);
    respond(200, 'Added to favorites');

case 'remove_favorite':
    $user = requireAuth();
    $data = getInput();
    getDB()->prepare("DELETE FROM favorites WHERE user_id = ? AND car_id = ?")->execute([$user['id'], $data['car_id'] ?? '']);
    respond(200, 'Removed from favorites');

case 'get_notifications':
    $user = requireAuth();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$user['id']]);
    $items = $stmt->fetchAll();
    $stmt2 = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt2->execute([$user['id']]);
    respond(200, 'ok', ['items' => $items, 'unread' => (int)$stmt2->fetchColumn()]);

case 'mark_notifications_read':
    $user = requireAuth();
    getDB()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user['id']]);
    respond(200, 'ok');

case 'get_conversations':
    $user = requireAuth();
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.*,
               CONCAT(u1.first_name, ' ', u1.last_name) AS client_name, u1.profile_photo AS client_photo,
               CONCAT(u2.first_name, ' ', u2.last_name) AS agent_name, u2.profile_photo AS agent_photo,
               (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.sender_id != ? AND m.is_read = 0) AS unread_count
        FROM conversations c
        JOIN users u1 ON u1.id = c.client_id
        JOIN users u2 ON u2.id = c.agent_id
        LEFT JOIN deleted_conversations dc ON dc.conversation_id = c.id AND dc.user_id = ?
        LEFT JOIN blocked_users bu ON (bu.blocker_id = ? AND bu.blocked_id IN (c.client_id, c.agent_id))
        WHERE (c.client_id = ? OR c.agent_id = ?)
          AND dc.id IS NULL
          AND bu.id IS NULL
        ORDER BY c.last_message_at DESC
    ");
    $stmt->execute([$user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
    $convs = $stmt->fetchAll();
    foreach ($convs as &$c) {
   $c['client_photo_url'] = getPhotoUrl($c['client_photo'] ?? '');
$c['agent_photo_url'] = getPhotoUrl($c['agent_photo'] ?? '');
    }
    respond(200, 'ok', $convs);

case 'get_messages':
    $user = requireAuth();
    $convId = intval($_GET['conversation_id'] ?? 0);
    if (!$convId) respond(400, 'conversation_id is required');
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM conversations WHERE id = ? AND (client_id = ? OR agent_id = ?)");
    $stmt->execute([$convId, $user['id'], $user['id']]);
    if (!$stmt->fetch()) respond(403, 'Access denied');
    $db->prepare("UPDATE messages SET is_read = 1, seen_at = NOW() WHERE conversation_id = ? AND sender_id != ? AND seen_at IS NULL")->execute([$convId, $user['id']]);
    $db->prepare("UPDATE messages SET delivered = 1 WHERE conversation_id = ? AND sender_id != ? AND delivered = 0")->execute([$convId, $user['id']]);
    $stmt = $db->prepare("
    SELECT m.*, 
           CONCAT(u.first_name, ' ', u.last_name) AS sender_name, 
           u.profile_photo AS sender_photo,
           cr.id AS reservation_id,
           cr.first_name AS res_first_name,
           cr.last_name AS res_last_name,
           cr.phone AS res_phone,
           cr.payment_method AS res_payment_method,
           cr.payment_file AS res_payment_file,
           cr.status AS res_status,
           cr.visited AS res_visited,
cr.car_id AS res_car_id,
c2.title AS res_car_name
    FROM messages m 
    JOIN users u ON u.id = m.sender_id 
    LEFT JOIN car_reservations cr ON cr.id = m.reservation_id
LEFT JOIN cars c2 ON c2.id = cr.car_id
WHERE m.conversation_id = ?
    ORDER BY m.sent_at ASC 
    LIMIT 100
");
    $stmt->execute([$convId]);
    respond(200, 'ok', $stmt->fetchAll());

case 'send_message':
    $user = requireAuth();
    $data = getInput();
    $agentId = intval($data['agent_id'] ?? 0);
    $message = trim($data['message'] ?? '');
    $carId = trim($data['car_id'] ?? '');
    $carName = trim($data['car_name'] ?? '');
    if (!$agentId || !$message) respond(400, 'agent_id and message are required');
    $db = getDB();
    $bStmt = $db->prepare("SELECT id FROM blocked_users WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)");
    $bStmt->execute([$user['id'], $agentId, $agentId, $user['id']]);
    if ($bStmt->fetch()) respond(403, 'Cannot send message to this user');
    $clientId = $user['role'] === 'client' ? $user['id'] : $agentId;
    $agentIdReal = $user['role'] === 'agent' ? $user['id'] : $agentId;
    $stmt = $db->prepare("SELECT id FROM conversations WHERE client_id = ? AND agent_id = ?");
    $stmt->execute([$clientId, $agentIdReal]);
    $conv = $stmt->fetch();
    if (!$conv) {
        $db->prepare("INSERT INTO conversations (client_id, agent_id, car_id, car_name) VALUES (?,?,?,?)")->execute([$clientId, $agentIdReal, $carId ?: null, $carName]);
        $convId = $db->lastInsertId();
    } else {
        $convId = $conv['id'];
    }
    $db->prepare("INSERT INTO messages (conversation_id, sender_id, message, delivered) VALUES (?,?,?,1)")->execute([$convId, $user['id'], $message]);
    $db->prepare("UPDATE conversations SET last_message = ?, last_message_at = NOW() WHERE id = ?")->execute([$message, $convId]);
    $recipientId = $user['id'] === $clientId ? $agentIdReal : $clientId;
    $senderName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $db->prepare("
        INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at)
        VALUES (?, ?, ?, ?, 'message', ?, NOW())
    ")->execute([
        $recipientId,
        $user['id'],
        $senderName . ' sent you a message',
        $message,
        'profile.php?tab=chat&conv=' . $convId
    ]);
    respond(200, 'Message sent', ['conversation_id' => $convId]);

case 'start_conversation':
    $user = requireAuth();
    $data = getInput();
    $agentId = intval($data['agent_id'] ?? 0);
    $carId = trim($data['car_id'] ?? '');
    $carName = trim($data['car_name'] ?? '');
    if (!$agentId) respond(400, 'agent_id is required');
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM conversations WHERE client_id = ? AND agent_id = ? AND car_id = ?");
$stmt->execute([$user['id'], $agentId, $carId]);
    $conv = $stmt->fetch();
    if (!$conv) {
        $db->prepare("INSERT INTO conversations (client_id, agent_id, car_id, car_name) VALUES (?,?,?,?)")->execute([$user['id'], $agentId, $carId ?: null, $carName]);
        $convId = $db->lastInsertId();
    } else {
        $convId = $conv['id'];
    }
    // إشعار للوسيط لما زبون يبدأ محادثة
    if (!$conv) { // فقط إذا محادثة جديدة
        $senderName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $db->prepare("
            INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at)
            VALUES (?, ?, ?, ?, 'message', ?, NOW())
        ")->execute([
            $agentId,
            $user['id'],
            'new message from' . $senderName,
            'wanna talks with you about' . ($carName ?: 'car'),
            'profile.php?tab=chat&conv=' . $convId
        ]);
    }
    respond(200, 'ok', ['conversation_id' => $convId]);

case 'delete_conversation':
    $user = requireAuth();
    $data = getInput();
    $convId = intval($data['conversation_id'] ?? 0);
    if (!$convId) respond(400, 'conversation_id is required');
    $db = getDB();
    $db->prepare("INSERT IGNORE INTO deleted_conversations (user_id, conversation_id) VALUES (?,?)")->execute([$user['id'], $convId]);
    respond(200, 'Conversation deleted');

case 'block_user':
    $user = requireAuth();
    $data = getInput();
    $blockedId = intval($data['blocked_id'] ?? 0);
    if (!$blockedId) respond(400, 'blocked_id is required');
    $db = getDB();
    $db->prepare("INSERT IGNORE INTO blocked_users (blocker_id, blocked_id) VALUES (?,?)")->execute([$user['id'], $blockedId]);
    respond(200, 'User blocked');

case 'unblock_user':
    $user = requireAuth();
    $data = getInput();
    $blockedId = intval($data['blocked_id'] ?? 0);
    if (!$blockedId) respond(400, 'blocked_id is required');
    $db = getDB();
    $db->prepare("DELETE FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?")->execute([$user['id'], $blockedId]);
    respond(200, 'User unblocked');

case 'mark_seen':
    $user = requireAuth();
    $data = getInput();
    $convId = intval($data['conversation_id'] ?? 0);
    if (!$convId) respond(400, 'conversation_id is required');
    $db = getDB();
    $db->prepare("UPDATE messages SET is_read = 1, seen_at = NOW(), delivered = 1 WHERE conversation_id = ? AND sender_id != ? AND seen_at IS NULL")->execute([$convId, $user['id']]);
    respond(200, 'ok');

case 'mark_delivered':
    $user = requireAuth();
    $data = getInput();
    $convId = intval($data['conversation_id'] ?? 0);
    if (!$convId) respond(400, 'conversation_id is required');
    $db = getDB();
    $db->prepare("UPDATE messages SET delivered = 1 WHERE conversation_id = ? AND sender_id != ? AND delivered = 0")->execute([$convId, $user['id']]);
    respond(200, 'ok');
case 'get_agent_conversations':
    $agentId = intval($_GET['agent_id'] ?? 0);
    if (!$agentId) respond(400, 'agent_id required');
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.*, 
               CONCAT(u.first_name,' ',u.last_name) AS client_name,
               u.profile_photo AS client_photo,
               u.is_online AS is_online,
               (SELECT COUNT(*) FROM messages m 
                WHERE m.conversation_id = c.id 
                AND m.sender_id = c.client_id 
                AND m.is_read = 0) AS unread_count
        FROM conversations c
        JOIN users u ON u.id = c.client_id
        WHERE c.agent_id = ?
        ORDER BY c.last_message_at DESC
    ");
    $stmt->execute([$agentId]);
    respond(200, 'ok', $stmt->fetchAll());

case 'get_agent_messages':
    $convId  = intval($_GET['conversation_id'] ?? 0);
    $agentId = intval($_GET['agent_id'] ?? 0);
    if (!$convId) respond(400, 'conversation_id required');
    $db = getDB();
    $stmt = $db->prepare("
    SELECT m.*, 
           CONCAT(u.first_name, ' ', u.last_name) AS sender_name,
           cr.id AS reservation_id,
           cr.first_name AS res_first_name,
           cr.last_name AS res_last_name,
           cr.phone AS res_phone,
           cr.payment_method AS res_payment_method,
           cr.payment_file AS res_payment_file,
           cr.status AS res_status,
           cr.visited AS res_visited,
cr.car_id AS res_car_id,
c2.title AS res_car_name
    FROM messages m 
    JOIN users u ON u.id = m.sender_id 
    LEFT JOIN car_reservations cr ON cr.id = m.reservation_id
LEFT JOIN cars c2 ON c2.id = cr.car_id
WHERE m.conversation_id = ?
    ORDER BY m.sent_at ASC 
    LIMIT 100
");
    $stmt->execute([$convId]);
    respond(200, 'ok', $stmt->fetchAll());

case 'send_agent_message':
    $data = getInput();
    $convId  = intval($data['conversation_id'] ?? 0);
    $agentId = intval($data['agent_id'] ?? 0);
    $clientId = intval($data['client_id'] ?? 0);
    $message = trim($data['message'] ?? '');
    if (!$convId || !$message) respond(400, 'Missing fields');
    $db = getDB();
    $db->prepare("INSERT INTO messages (conversation_id, sender_id, message, delivered) VALUES (?,?,?,1)")->execute([$convId, $agentId, $message]);
    $db->prepare("UPDATE conversations SET last_message=?, last_message_at=NOW() WHERE id=?")->execute([$message, $convId]);
    // جيب اسم الوسيط
    $agentInfo = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $agentInfo->execute([$agentId]);
    $agentRow = $agentInfo->fetch();
    $agentName = trim(($agentRow['first_name'] ?? '') . ' ' . ($agentRow['last_name'] ?? ''));
    
    $db->prepare("INSERT INTO notifications (user_id, sender_id, title, message, type, link, created_at)
        VALUES (?, ?, ?, ?, 'message', ?, NOW())
    ")->execute([
        $clientId,
        $agentId,
        'sent you a message' . $agentName,
        $message,
        'profile.php?tab=chat&conv=' . $convId
    ]);
    respond(200, 'ok');
    case 'get_agent_notifications':
    $agentId = intval($_GET['agent_id'] ?? 0);
    if (!$agentId && isset($_SESSION['user_id'])) $agentId = $_SESSION['user_id'];
    if (!$agentId) respond(400, 'agent_id required');
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$agentId]);
    $items = $stmt->fetchAll();
    $stmt2 = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt2->execute([$agentId]);
    respond(200, 'ok', ['items' => $items, 'unread' => (int)$stmt2->fetchColumn()]);
    case 'get_agent_requests':
    $agent_id = intval($_GET['agent_id'] ?? 0);
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            c.id,
            c.last_message as message,
            c.created_at,
            c.car_name as car_title,
            u.id as client_id,
            CONCAT(u.first_name, ' ', u.last_name) as client_name,
            0 as is_read,
            cr.id as reservation_id,
            cr.status as reservation_status
        FROM conversations c
        LEFT JOIN users u ON c.client_id = u.id
        LEFT JOIN car_reservations cr ON cr.conversation_id = c.id
        WHERE c.agent_id = ?
        ORDER BY c.last_message_at DESC
    ");
    $stmt->execute([$agent_id]);
    respond(200, 'ok', ['data' => $stmt->fetchAll()]);
    break;
    case 'get_cars':
    $db = getDB();
    $where = ['c.status = "available"'];
    $params = [];

    if (!empty($_GET['q'])) {
        $where[] = '(c.title LIKE ? OR c.brand LIKE ? OR c.description LIKE ?)';
        $q = '%' . $_GET['q'] . '%';
        $params = array_merge($params, [$q, $q, $q]);
    }
    if (!empty($_GET['brand'])) {
        $where[] = 'LOWER(c.brand) = ?';
        $params[] = strtolower($_GET['brand']);
    }

    $sql = "
        SELECT c.*, 
               cp.photo_path AS first_photo,
               CONCAT(u.first_name,' ',u.last_name) AS agent_name,
               u.phone AS agent_phone
        FROM cars c
        LEFT JOIN car_photos cp ON cp.car_id = c.id AND cp.is_primary = 1
        LEFT JOIN users u ON u.id = c.agent_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY c.created_at DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();
    foreach ($cars as &$car) {
        $car['photo_url'] = $car['first_photo'] 
            ? BASE_URL . $car['first_photo'] 
            : null;
    }
    respond(200, 'ok', $cars);
    case 'change_password':
    $user = requireAuth();
    $data = getInput();
    $oldPass = $data['old_password'] ?? '';
    $newPass = $data['new_password'] ?? '';
    if (!$oldPass || !$newPass) respond(400, 'Fill all fields');
    if (strlen($newPass) < 8) respond(400, 'Minimum 8 characters');
    $db = getDB();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$row = $stmt->fetch();
if (!password_verify($oldPass, $row['password_hash'])) respond(400, 'Current password is wrong');
$hash = password_hash($newPass, PASSWORD_BCRYPT);
$db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $user['id']]);
$headers = getallheaders();
$currentToken = substr($headers['Authorization'] ?? $headers['authorization'] ?? '', 7);
$db->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_token != ?")->execute([$user['id'], $currentToken]);
    respond(200, 'Password updated');
    case 'start_trial':
    $db = getDB();
    $uid = $_SESSION['user_id'] ?? null;
    if (!$uid) { respond(400, 'Not logged in'); break; }
    $stmt = $db->prepare("UPDATE users SET trial_started_at = NOW(), trial_used = 1 WHERE id = ? AND trial_used = 0");
    $stmt->execute([$uid]);
    respond(200, 'Trial started');
    break;

case 'get_trial_status':
    $user = requireAuth();
    $db = getDB();
    $stmt = $db->prepare("SELECT trial_started_at, trial_used FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();
    $trialSeconds = 420; // 7 دقائق
    $remaining = 0;
    $started = false;
    if ($row['trial_started_at']) {
        $started = true;
        $elapsed = time() - strtotime($row['trial_started_at']);
        $remaining = max(0, $trialSeconds - $elapsed);
    }
    respond(200, 'ok', [
        'trial_used' => (bool)$row['trial_used'],
        'trial_started' => $started,
        'remaining_seconds' => $remaining,
        'expired' => $started && $remaining <= 0
    ]);
    case 'start_sub_test':
    $uid = $_SESSION['user_id'] ?? null;
    if (!$uid) { respond(400, 'Not logged in'); break; }
    $db = getDB();
    $db->prepare("UPDATE subscriptions SET test_started_at = NOW() WHERE user_id = ? ORDER BY created_at DESC LIMIT 1")->execute([$uid]);
    respond(200, 'ok');
    break;
    case 'get_reservation':
    $request_id = intval($_GET['request_id'] ?? 0);
    $db = getDB();
    $stmt = $db->prepare("
        SELECT cr.*, c.title AS car_title
        FROM car_reservations cr
        LEFT JOIN cars c ON c.id = cr.car_id
        WHERE cr.id = ?
    ");
    $stmt->execute([$request_id]);
    $row = $stmt->fetch();
    respond($row ? 200 : 404, $row ? 'ok' : 'Not found', $row ?: null);
    break;

case 'decide_reservation':
    $body   = json_decode(file_get_contents('php://input'), true);
    $res_id = intval($body['reservation_id'] ?? 0);
    $status = in_array($body['status'], ['accepted','refused']) ? $body['status'] : 'pending';
    $visited= intval($body['visited'] ?? 0);
    $db = getDB();

    $get = $db->prepare("SELECT car_id, client_id FROM car_reservations WHERE id = ?");
    $get->execute([$res_id]);
    $res = $get->fetch();

    if (!$res) { respond(404, 'Not found'); break; }

    $db->prepare("UPDATE car_reservations SET status=?, visited=? WHERE id=?")->execute([$status, $visited, $res_id]);

    if ($status === 'accepted') {
    if ($visited === 1) {
        // الزبون جاء — السيارة تبقى reserved
        $db->prepare("
            UPDATE cars SET reservation_status = 'reserved' WHERE id = ?
        ")->execute([$res['car_id']]);
        $db->prepare("
            UPDATE car_reservations SET visited = 1 WHERE id = ?
        ")->execute([$res_id]);
        $db->prepare("
            INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at)
            VALUES (?, ?, ?, 'success', ?, 0, NOW())
        ")->execute([
            $res['client_id'],
            'Visit Confirmed',
            'The agent has confirmed your visit. Your car remains reserved.',
            'profile.php?tab=tracking'
        ]);
    } else {
        // حجز جديد — 4 أيام
        $until = date('Y-m-d H:i:s', strtotime('+4 days'));
        $db->prepare("
            UPDATE cars SET reservation_status = 'reserved', reserved_until = ? WHERE id = ?
        ")->execute([$until, $res['car_id']]);
        $db->prepare("
            UPDATE car_reservations SET reserved_until = ?, visited = 0 WHERE id = ?
        ")->execute([$until, $res_id]);
        $db->prepare("
            INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at)
            VALUES (?, ?, ?, 'reservation', ?, 0, NOW())
        ")->execute([
            $res['client_id'],
            'Reservation Confirmed',
            'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.',
            'profile.php?tab=tracking'
        ]);
    }
} elseif ($status === 'refused') {
    // الوسيط رفض أو الزبون ما جاش
    $db->prepare("UPDATE cars SET reservation_status = 'available', reserved_until = NULL WHERE id = ?
    ")->execute([$res['car_id']]);
    $db->prepare("UPDATE car_reservations SET visited = 0 WHERE id = ?
    ")->execute([$res_id]);
    $db->prepare("INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at)
        VALUES (?, ?, ?, 'reservation', ?, 0, NOW())
    ")->execute([
        $res['client_id'],
        'Reservation Cancelled',
        'Your reservation has been cancelled and the car is now available again.',
        'profile.php?tab=tracking'
    ]);
}

    respond(200, 'ok');
    break;
    case 'respond_reservation':
    $user = requireAuth();
    $data = getInput();
    $resId = intval($data['reservation_id'] ?? 0);
    $action_val = in_array($data['action'], ['accepted','refused']) ? $data['action'] : '';
    if (!$resId || !$action_val) respond(400, 'Missing fields');
    $db = getDB();
    $get = $db->prepare("SELECT car_id, client_id FROM car_reservations WHERE id = ?");
    $get->execute([$resId]);
    $res = $get->fetch();
    if (!$res) respond(404, 'Reservation not found');
    $db->prepare("UPDATE car_reservations SET status = ? WHERE id = ?")->execute([$action_val, $resId]);
    if ($action_val === 'accepted') {
        $until = date('Y-m-d H:i:s', strtotime('+4 days'));
        $db->prepare("UPDATE cars SET reservation_status='reserved', reserved_until=? WHERE id=?")->execute([$until, $res['car_id']]);
        $db->prepare("UPDATE car_reservations SET reserved_until=? WHERE id=?")->execute([$until, $resId]);
        $db->prepare("
    INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at)
    VALUES (?, ?, ?, 'reservation', ?, 0, NOW())
")->execute([
    $res['client_id'],
    'Reservation Confirmed',
    'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.',
    'profile.php?tab=tracking'
]);
    } else {
        $db->prepare("UPDATE cars SET reservation_status='available', reserved_until=NULL WHERE id=?")->execute([$res['car_id']]);
        $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?,?,?,'reservation',0,NOW())")->execute([$res['client_id'], '❌ Reservation Refused', 'Unfortunately your reservation request was not accepted.']);
    }
    respond(200, 'ok');
    case 'update_shipment_stage':
    $body = json_decode(file_get_contents('php://input'), true);
    $reservation_id = intval($body['reservation_id'] ?? 0);
    $stage          = trim($body['stage'] ?? '');
    $stage_note     = trim($body['stage_note'] ?? '');
    $agent_id       = intval($body['agent_id'] ?? 0);

    $allowed_stages = [
        'purchased'  => 'Order Confirmed in China',
        'shipped'    => 'Shipped — On the Way',
        'customs'    => 'Customs Clearance',
        'warehouse'  => 'In Warehouse',
        'delivery'   => 'Ready for Delivery',
        'delivered'  => 'Delivered',
    ];

    if (!$reservation_id || !isset($allowed_stages[$stage])) {
        respond(400, 'Invalid data'); break;
    }

    $stage_label = $allowed_stages[$stage];
    $db = getDB();

    $get = $db->prepare("
        SELECT cr.client_id, cr.car_id, c.title AS car_title
        FROM car_reservations cr
        JOIN cars c ON c.id = cr.car_id
        WHERE cr.id = ?
    ");
    $get->execute([$reservation_id]);
    $res = $get->fetch();

    if (!$res) { respond(404, 'Reservation not found'); break; }

    $check = $db->prepare("SELECT id FROM shipment_tracking WHERE reservation_id = ?");
    $check->execute([$reservation_id]);

    if ($check->fetch()) {
        $db->prepare("
            UPDATE shipment_tracking
            SET stage = ?, stage_label = ?, stage_note = ?, updated_at = NOW()
            WHERE reservation_id = ?
        ")->execute([$stage, $stage_label, $stage_note, $reservation_id]);
    } else {
        $db->prepare("
            INSERT INTO shipment_tracking
                (reservation_id, car_id, client_id, agent_id, stage, stage_label, stage_note)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $reservation_id,
            $res['car_id'],
            $res['client_id'],
            $agent_id,
            $stage,
            $stage_label,
            $stage_note
        ]);
    }

    $db->prepare("
        INSERT INTO notifications (user_id, sender_id, title, message, type, link, is_read, created_at)
        VALUES (?, ?, ?, ?, 'info', ?, 0, NOW())
    ")->execute([
        $res['client_id'],
        $agent_id,
        'Car Status Update: ' . $stage_label,
        ($res['car_title'] ?? 'Your car') . ' — ' . $stage_label . ($stage_note ? '. ' . $stage_note : ''),
        'profile.php?tab=tracking'
    ]);

    respond(200, 'ok', ['stage' => $stage, 'label' => $stage_label]);
    break;

case 'get_client_reservations':
    $client_id = intval($_GET['client_id'] ?? 0);
    if (!$client_id) { respond(400, 'client_id required'); break; }
    $db = getDB();
    $stmt = $db->prepare("
        SELECT cr.id AS reservation_id, cr.status, cr.visited, cr.created_at,
               c.title AS car_title, c.id AS car_id,
               st.stage, st.stage_label, st.stage_note,
               st.updated_at AS tracking_updated,
               CONCAT(u.first_name, ' ', u.last_name) AS agent_name
        FROM car_reservations cr
        JOIN cars c ON c.id = cr.car_id
        LEFT JOIN shipment_tracking st ON st.reservation_id = cr.id
        JOIN users u ON u.id = cr.agent_id
        WHERE cr.client_id = ?
        ORDER BY cr.created_at DESC
    ");
    $stmt->execute([$client_id]);
    respond(200, 'ok', $stmt->fetchAll());
    break;

case 'get_agent_reservations_for_tracking':
    $agent_id = intval($_GET['agent_id'] ?? 0);
    if (!$agent_id) { respond(400, 'agent_id required'); break; }
    $db = getDB();
    $stmt = $db->prepare("
        SELECT cr.id AS reservation_id, cr.status, cr.visited, cr.created_at,
               cr.client_id,
               CONCAT(cl.first_name, ' ', cl.last_name) AS client_name,
               c.title AS car_title, c.id AS car_id,
               st.stage, st.stage_label, st.stage_note
        FROM car_reservations cr
        JOIN cars c ON c.id = cr.car_id
        JOIN users cl ON cl.id = cr.client_id
        LEFT JOIN shipment_tracking st ON st.reservation_id = cr.id
        WHERE cr.agent_id = ? AND cr.status = 'accepted'
        ORDER BY cr.created_at DESC
    ");
    $stmt->execute([$agent_id]);
    respond(200, 'ok', $stmt->fetchAll());
    break;
    case 'get_car_status':
  $db = getDB();
  $car_id = intval($_GET['car_id'] ?? 0);
  $conv_id = intval($_GET['conv_id'] ?? 0);
  if (!$car_id && $conv_id) {
    $s = $db->prepare("SELECT car_id FROM conversations WHERE id = ?");
    $s->execute([$conv_id]);
    $row = $s->fetch();
    $car_id = $row ? intval($row['car_id']) : 0;
  }
  if (!$car_id) respond(200, 'ok', ['reservation_status' => 'available']);
  $stmt = $db->prepare("SELECT reservation_status FROM cars WHERE id = ?");
  $stmt->execute([$car_id]);
  $car = $stmt->fetch();
  respond(200, 'ok', $car ?: ['reservation_status' => 'available']);
  break;
  case 'save_payment_info':
case 'save_payment_info_session':
    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
    } else {
        $user = requireAuth();
        $uid = $user['id'];
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    $stmt = $db->prepare("UPDATE agent_profiles SET ccp_account=?, ccp_rip=?, ccp_owner=?, deposit_amount=? WHERE user_id=?");
    $stmt->execute([
        $data['ccp_account'] ?? null,
        $data['ccp_rip'] ?? null,
        $data['ccp_owner'] ?? null,
        $data['deposit_amount'] ?? null,
        $uid
    ]);
    respond(200, 'ok', ['success'=>true]);
    break;

case 'get_payment_info':
    $db = getDB();
    $agentId = intval($_GET['agent_id'] ?? 0);
    if (!$agentId) { respond(400, 'agent_id required'); break; }
    $stmt = $db->prepare("SELECT ccp_account, ccp_rip, ccp_owner, deposit_amount FROM agent_profiles WHERE user_id=?");
    $stmt->execute([$agentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    respond(200, 'ok', $row ?: []);
    break;
    case 'get_pending_visit_reservations':
        $db = getDB();
    $agentId = $_GET['agent_id'];
    $stmt = $db->prepare("
        SELECT r.*, u.first_name, u.last_name 
        FROM car_reservations r
        JOIN users u ON u.id = r.client_id
        WHERE r.agent_id = ? 
        AND r.status = 'accepted' 
        AND (r.visited IS NULL OR r.visited = 0)
        AND r.created_at >= DATE_SUB(NOW(), INTERVAL 5 DAY)
        ORDER BY r.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$agentId]);
    $reservation = $stmt->fetch();
    echo json_encode([
        'success' => true,
        'data' => $reservation ? [$reservation] : []
    ]);
    break;
    case 'get_stats':
    $db = getDB();
    $agents = $db->query("SELECT COUNT(*) FROM users WHERE role ='agent' AND status = 'approved'")->fetchColumn();
    $cars = $db->query("SELECT COUNT(*) FROM cars WHERE status = 'available'")->fetchColumn();
    $wilayas = $db->query("SELECT COUNT(DISTINCT wilaya) FROM users WHERE role = 'agent' AND status = 'approved' AND wilaya IS NOT NULL AND wilaya != ''")->fetchColumn();
    respond(200, 'ok', [
        'agents'  => (int)$agents,
        'cars'    => (int)$cars,
        'wilayas' => (int)$wilayas
    ]);
    break;
default:
    respond(404, 'Action not found: ' . $action);
}
