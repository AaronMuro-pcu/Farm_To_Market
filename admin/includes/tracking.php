<?php
// Records who logs in, who logs out and who is online.
// Your sign-in and logout code (and your farmer/buyer pages) call these.
// They never stop a login if something goes wrong: errors are swallowed.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/** Call right after a successful password check. */
function track_login(int $userId): void {
    try {
        $pdo = db();
        $now = date('Y-m-d H:i:s');
        $pdo->prepare('UPDATE users SET last_login = ?, last_seen = ?, is_online = 1 WHERE id = ?')
            ->execute([$now, $now, $userId]);
        $pdo->prepare("INSERT INTO login_activity (user_id, action, created_at) VALUES (?, 'login', ?)")
            ->execute([$userId, $now]);
    } catch (Throwable $ex) {
        // ignore: tracking must never block a login
    }
}

/** Call just before destroying the session on logout. */
function track_logout(int $userId): void {
    try {
        $pdo = db();
        $now = date('Y-m-d H:i:s');
        $pdo->prepare('UPDATE users SET is_online = 0 WHERE id = ?')->execute([$userId]);
        $pdo->prepare("INSERT INTO login_activity (user_id, action, created_at) VALUES (?, 'logout', ?)")
            ->execute([$userId, $now]);
    } catch (Throwable $ex) {
    }
}

/** Call at the top of every logged-in farmer/buyer page. Keeps "online now" accurate. */
function track_seen(int $userId): void {
    try {
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (isset($_SESSION['_seen_at']) && time() - $_SESSION['_seen_at'] < 60) {
                return; // at most one write a minute per user
            }
            $_SESSION['_seen_at'] = time();
        }
        db()->prepare('UPDATE users SET last_seen = ?, is_online = 1 WHERE id = ?')
            ->execute([date('Y-m-d H:i:s'), $userId]);
    } catch (Throwable $ex) {
    }
}
