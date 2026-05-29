<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $errstr]);
    exit;
});

require 'config.php';
header('Content-Type: application/json');

// قراءة البيانات حسب نوع الطلب
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    // client يرسل JSON
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    $role          = $data['role']        ?? '';
    $first_name    = $data['first_name']  ?? '';
    $last_name     = $data['last_name']   ?? '';
    $email         = $data['email']       ?? '';
    $phone         = $data['phone']       ?? '';
    $password      = $data['password']    ?? '';
    $national_id   = '';
    $id_card_name  = '';
    $company_name  = '';
    $rc_number     = '';
    $rc_owner_name = '';
    $id_card_file  = null;
    $rc_file       = null;
} else {
    // agent يرسل FormData
    $role          = $_POST['role']          ?? '';
    $first_name    = $_POST['first_name']    ?? '';
    $last_name     = $_POST['last_name']     ?? '';
    $email         = $_POST['email']         ?? '';
    $phone         = $_POST['phone']         ?? '';
    $password      = $_POST['password']      ?? '';
    $national_id   = $_POST['national_id']   ?? '';
    $id_card_name  = $_POST['id_card_name']  ?? '';
    $company_name  = $_POST['company_name']  ?? '';
    $rc_number     = $_POST['rc_number']     ?? '';
    $rc_owner_name = $_POST['rc_owner_name'] ?? '';
    $id_card_file  = $_FILES['id_card_file'] ?? null;
    $rc_file       = $_FILES['rc_file']      ?? null;
}

// التحقق من البيانات الأساسية
if (!$role || !$email || !$phone || !$password) {
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

// التحقق من صحة الإيميل
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

// التحقق إذا الإيميل موجود مسبقاً
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Email already exists']);
    exit;
}

// التحقق إذا الهاتف موجود مسبقاً
$stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute([$phone]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Phone already exists']);
    exit;
}

// تشفير كلمة السر
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// حفظ المستخدم في جدول users
$status = ($role === 'agent') ? 'pending' : 'active';
$stmt = $pdo->prepare("INSERT INTO users 
    (role, first_name, last_name, email, phone, password_hash, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$role, $first_name, $last_name, $email, $phone, $password_hash, $status]);

$user_id = $pdo->lastInsertId();
// حفظ صورة الـ client
if ($role === 'client') {
    $upload_dir = 'uploads/clients/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/photos/client_' . $user_id . '.' . $ext;
        $full_path = __DIR__ . '/' . $filename;
        if (!is_dir('uploads/photos')) mkdir('uploads/photos', 0755, true);
        move_uploaded_file($file['tmp_name'], $full_path);
        // حفظ مسار الصورة في DB
        $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?")
            ->execute([$filename, $user_id]);
    }
}

// إذا agent: حفظ الملفات وبيانات الـ profile
if ($role === 'agent') {
    $upload_dir = 'uploads/agents/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
// ✅ حفظ صورة البروفايل للـ agent
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/photos/agent_' . $user_id . '.' . $ext;
        $full_path = __DIR__ . '/' . $filename;
        move_uploaded_file($file['tmp_name'], $full_path);
        $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?")
            ->execute([$filename, $user_id]);
    }
    $id_card_saved = '';
    $rc_saved      = '';

    if ($id_card_file && $id_card_file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($id_card_file['name'], PATHINFO_EXTENSION);
        $id_card_saved = $upload_dir . 'id_' . $user_id . '.' . $ext;
        move_uploaded_file($id_card_file['tmp_name'], $id_card_saved);
    }

    if ($rc_file && $rc_file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($rc_file['name'], PATHINFO_EXTENSION);
        $rc_saved = $upload_dir . 'rc_' . $user_id . '.' . $ext;
        move_uploaded_file($rc_file['tmp_name'], $rc_saved);
    }

    $stmt2 = $pdo->prepare("INSERT INTO agent_profiles 
        (user_id, first_name, last_name, national_id, id_card_name, 
         company_name, rc_number, rc_owner_name, id_card_file, rc_file) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->execute([
        $user_id, $first_name, $last_name, $national_id, $id_card_name,
        $company_name, $rc_number, $rc_owner_name, $id_card_saved, $rc_saved
    ]);
}
if ($role === 'agent') {
    $adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
    $pdo->prepare("INSERT INTO notifications (user_id, sender_id, title, message, type, created_at) VALUES (?, ?, 'New Agent Registration', ?, 'info', NOW())")
        ->execute([$adminId, $user_id, $first_name.' '.$last_name.' has registered and is awaiting approval.']);
}
echo json_encode(['success' => true, 'user_id' => $user_id]);
?>