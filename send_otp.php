<?php
require 'config.php';
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

$data    = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? '';
$method  = $data['method'] ?? 'email';

if (!$user_id) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$code    = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);


$stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?");
$stmt->execute([$user_id]);

$stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, code, method, expires_at) VALUES (?, ?, ?, ?)");
$stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, code, method, expires_at) 
    VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
$stmt->execute([$user_id, $code, $method]);

$stmt = $pdo->prepare("SELECT email, first_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'nuussa7@gmail.com';
$mail->Password   = 'ugmduxgzepoqgbqt';
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;
$mail->SMTPDebug  = 0; // ← هذا كان السبب، كان 2 فيطبع نص يخرب الـ JSON
$mail->setFrom('nuussa7@gmail.com', 'China2DZ');
$mail->addAddress($user['email']);
$mail->Subject = 'China2DZ - Verification Code';
$mail->Body    = "Hello " . $user['first_name'] . ",\n\nYour verification code is: " . $code . "\n\nThis code expires in 10 minutes.\n\nChina2DZ Team";

try {
    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to send email: ' . $mail->ErrorInfo]);
}
?>