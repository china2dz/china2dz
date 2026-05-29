<?php
require 'config.php';
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

$data  = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (!$email) {
    echo json_encode(['error' => 'Email is required']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['error' => 'No account found with this email']);
    exit;
}

$code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

$pdo->prepare("UPDATE users SET reset_code=?, reset_exp=DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id=?")
    ->execute([$code, $user['id']]);

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'nuussa7@gmail.com';
$mail->Password   = 'ugmduxgzepoqgbqt';
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;
$mail->SMTPDebug  = 0;
$mail->setFrom('nuussa7@gmail.com', 'China2DZ');
$mail->addAddress($email);
$mail->Subject = 'China2DZ - Reset Password';
$mail->Body    = "Hello " . $user['first_name'] . ",\n\nYour password reset code is: " . $code . "\n\nThis code expires in 30 minutes.\n\nChina2DZ Team";

try {
    $mail->send();
    echo json_encode(['success' => true, 'user_id' => $user['id']]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to send email: ' . $mail->ErrorInfo]);
}
?>