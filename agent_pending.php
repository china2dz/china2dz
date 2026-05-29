<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.html'); exit;
}

require_once 'config.php';

$stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user && $user['status'] === 'approved') {
    $sub = $pdo->prepare("SELECT status, end_date FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $sub->execute([$_SESSION['user_id']]);
    $subscription = $sub->fetch();
    
    if ($subscription && $subscription['status'] === 'approved' && 
        $subscription['end_date'] && strtotime($subscription['end_date']) > time()) {
        header('Location: agents.php'); exit;
    }

    // دفع وينتظر قبول → وجهه لصفحة الانتظار
    if ($subscription && $subscription['status'] === 'pending') {
        header('Location: subscription_expired.php'); exit;
    }

    // approved بدون subscription أو trial → اعرض صفحة الاختيار
    define('SHOW_TRIAL_CHOICE', true);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <script>
setInterval(function() {
  fetch('check_status.php')
    .then(r => r.json())
    .then(data => {
      if (data.status === 'approved') {
        window.location.reload();
      }
    });
}, 5000);
</script>
    <meta charset="UTF-8">
    <title>pending approval </title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: #0d0d0d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            max-width: 480px;
            width: 90%;
        }
        .icon { font-size: 60px; margin-bottom: 20px; }
        h2 { color: #fff; font-size: 22px; margin-bottom: 12px; }
        p { color: rgba(255,255,255,0.45); font-size: 14px; line-height: 1.7; }
        .logout {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 28px;
            background: rgba(217,31,38,0.15);
            color: #ff5057;
            border: 1px solid rgba(217,31,38,0.3);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }
    </style>
    <script src="theme.js"></script>
</head>
<body>
<div class="card">
    <?php if (defined('SHOW_TRIAL_CHOICE')): ?>
    <div class="icon">🎉</div>
    <h2>Your account is approved!</h2>
    <p>Choose how you want to continue:</p>
    <button onclick="startTrial()" style="display:block;width:100%;margin-top:20px;padding:14px;background:rgba(62,207,142,0.15);color:#3ecf8e;border:1px solid rgba(62,207,142,0.3);border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">
        🚀 Start 7-day free trial → Access dashboard
    </button>
    <button onclick="maybeLater()" style="display:block;width:100%;margin-top:12px;padding:14px;background:rgba(255,255,255,0.05);color:rgba(255,255,255,0.5);border:1px solid rgba(255,255,255,0.1);border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">
        ⏸ Maybe later → Browse site without dashboard
    </button>
<?php else: ?>
    <div class="icon">⏳</div>
    <h2>your request is under review</h2>
    <p>your request has been received successfully.<br>
    the admin will review your request and activate your account once approved.</p>
<?php endif; ?>
<a href="logout.php" class="logout">logout</a>
</div>
<script>
async function startTrial() {
    const res = await fetch('start_trial.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) window.location.href = 'index.php';
}
function maybeLater() {
    window.location.href = 'index.php';
}
</script>
</body>
</html>