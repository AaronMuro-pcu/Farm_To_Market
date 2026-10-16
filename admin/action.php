<?php
// Handles the buttons: Suspend / Activate, Delete, Approve, Reject.
// POST only, admin only, and every request needs the page's CSRF token.

require_once __DIR__ . '/includes/auth.php';

$return = 'admin.php';
$r = (string)($_POST['return'] ?? '');
if (preg_match('/^(admin|users|approvals|activity|reports)\.php(\?[A-Za-z0-9=&%_.+\-]*)?$/', $r)) {
    $return = $r;
}

function back(string $to, string $type, string $msg): void {
    flash_set($type, $msg);
    header('Location: ' . $to);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $return);
    exit();
}
if (!csrf_valid($_POST['csrf'] ?? '')) {
    back($return, 'error', 'Your session expired. Please try again.');
}

$id  = (int)($_POST['id'] ?? 0);
$act = (string)($_POST['act'] ?? '');
$pdo = db();

$q = $pdo->prepare('SELECT id, name, role, status FROM users WHERE id = ?');
$q->execute([$id]);
$u = $q->fetch();

if (!$u || !in_array($u['role'], ['farmer', 'buyer'], true)) {
    back($return, 'error', 'That account was not found.');
}
$name = $u['name'];

switch ($act) {
    case 'toggle':
        if (!in_array($u['status'], ['active', 'suspended'], true)) {
            back($return, 'error', $name . ' is not an active member yet.');
        }
        $new = $u['status'] === 'active' ? 'suspended' : 'active';
        $pdo->prepare('UPDATE users SET status = ?, is_online = 0 WHERE id = ?')->execute([$new, $id]);
        back($return, 'success', $new === 'suspended' ? "$name's account was suspended." : "$name's account was reactivated.");

    case 'delete':
        $pdo->beginTransaction();
        $pdo->prepare('DELETE FROM login_activity WHERE user_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        $pdo->commit();
        back($return, 'success', "$name's account was deleted.");

    case 'approve':
        if ($u['role'] !== 'farmer' || $u['status'] !== 'pending') {
            back($return, 'error', $name . ' is not waiting for approval.');
        }
        $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
        back($return, 'success', "$name was approved and can now sell.");

    case 'reject':
        if ($u['role'] !== 'farmer' || $u['status'] !== 'pending') {
            back($return, 'error', $name . ' is not waiting for approval.');
        }
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        back($return, 'success', "$name's application was rejected.");

    default:
        back($return, 'error', 'Unknown action.');
}
