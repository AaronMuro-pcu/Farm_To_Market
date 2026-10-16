<?php
// Include this first on every admin page.
// Only logged-in users whose role is "admin" get through.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ' . LOGIN_URL);
    exit();
}
