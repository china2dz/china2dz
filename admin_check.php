<?php
session_start();
require_once 'config.php';

// إذا مش مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// إذا مش admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}

// كل شيء OK — فتح admin.php
header('Location: admin.php');
exit;
?>