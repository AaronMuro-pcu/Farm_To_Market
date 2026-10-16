<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracking.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    track_logout((int)$_SESSION['user_id']);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: ' . LOGIN_URL);
exit();
