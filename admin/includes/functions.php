<?php
// Shared helpers for every admin page.

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** One PDO connection for the whole request. */
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    try {
        $dsn = defined('DB_DSN')
            ? DB_DSN
            : 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $pdo->exec("SET time_zone = '+08:00'"); // same clock as date_default_timezone_set()
        } else {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        exit('<!DOCTYPE html><meta charset="utf-8"><title>Database error</title>'
           . '<body style="font-family:sans-serif;max-width:560px;margin:15vh auto;line-height:1.6">'
           . '<h2>Could not connect to the database</h2>'
           . '<p>Start MySQL in the XAMPP Control Panel, then check <code>DB_NAME</code>, '
           . '<code>DB_USER</code> and <code>DB_PASS</code> in <code>admin/includes/config.php</code>.</p></body>');
    }
    return $pdo;
}

/* ---------- Dates ---------- */

function today_start(): string  { return date('Y-m-d 00:00:00'); }
function online_cutoff(): string { return date('Y-m-d H:i:s', time() - ONLINE_MINUTES * 60); }

function when($ts): string {
    $t = strtotime((string)$ts);
    if (!$t) return '—';
    if (date('Y-m-d', $t) === date('Y-m-d')) return 'Today, ' . date('g:i A', $t);
    if (date('Y-m-d', $t) === date('Y-m-d', strtotime('yesterday'))) return 'Yesterday, ' . date('g:i A', $t);
    return date('M j, Y', $t);
}

function stamp($ts): string {
    $t = strtotime((string)$ts);
    return $t ? date('M j, Y', $t) . ' · ' . date('g:i A', $t) : '—';
}

function clock($ts): string {
    $t = strtotime((string)$ts);
    return $t ? date('g:i A', $t) : '—';
}

/* ---------- Badges ---------- */

function role_badge(string $role): string {
    $class = $role === 'farmer' ? 'ok' : 'info';
    return '<span class="badge badge-' . $class . '">' . e(ucfirst($role)) . '</span>';
}

function status_badge(string $status): string {
    $class = $status === 'active' ? 'ok' : ($status === 'pending' ? 'warn' : 'bad');
    return '<span class="badge badge-' . $class . '">' . e(ucfirst($status)) . '</span>';
}

function dot(bool $on): string {
    return '<span class="dot' . ($on ? ' dot-on' : '') . '"></span>';
}

function is_online(array $u): bool {
    return !empty($u['is_online']) && !empty($u['last_seen']) && $u['last_seen'] >= online_cutoff();
}

/* ---------- Numbers shown on several pages ---------- */

function pending_count(PDO $pdo): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'farmer' AND status = 'pending'")->fetchColumn();
}

/** Totals, suspended, logged in today and online now, for farmers and buyers. */
function member_stats(PDO $pdo): array {
    $blank = ['total' => 0, 'suspended' => 0, 'today' => 0, 'online' => 0];
    $s = ['farmer' => $blank, 'buyer' => $blank];

    $q = $pdo->query("SELECT role, status, COUNT(*) AS c FROM users
                      WHERE role IN ('farmer','buyer') AND status <> 'pending'
                      GROUP BY role, status");
    foreach ($q as $r) {
        $s[$r['role']]['total'] += (int)$r['c'];
        if ($r['status'] === 'suspended') $s[$r['role']]['suspended'] += (int)$r['c'];
    }

    $q = $pdo->prepare("SELECT u.role, COUNT(DISTINCT l.user_id) AS c
                        FROM login_activity l JOIN users u ON u.id = l.user_id
                        WHERE l.action = 'login' AND l.created_at >= ? AND u.role IN ('farmer','buyer')
                        GROUP BY u.role");
    $q->execute([today_start()]);
    foreach ($q as $r) $s[$r['role']]['today'] = (int)$r['c'];

    $q = $pdo->prepare("SELECT role, COUNT(*) AS c FROM users
                        WHERE role IN ('farmer','buyer') AND status = 'active' AND is_online = 1 AND last_seen >= ?
                        GROUP BY role");
    $q->execute([online_cutoff()]);
    foreach ($q as $r) $s[$r['role']]['online'] = (int)$r['c'];

    return $s;
}

/* ---------- Searching and paging ---------- */

/** Escape % and _ so a search for "50%" does not match everything. Use with ESCAPE '!'. */
function like(string $s): string {
    return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $s) . '%';
}

function paginate(int $total, int $page, int $per = PER_PAGE): array {
    $pages = max(1, (int)ceil($total / $per));
    $page  = min(max(1, $page), $pages);
    return [$page, $pages, ($page - 1) * $per];
}

function pager(int $total, int $page, int $pages, string $base, array $params): string {
    if ($total === 0) return '';
    $from = ($page - 1) * PER_PAGE + 1;
    $to   = min($total, $page * PER_PAGE);
    $url = function (int $p) use ($base, $params): string {
        $q = array_filter($params, fn($v) => $v !== '' && $v !== null);
        if ($p > 1) $q['page'] = $p;
        return $base . ($q ? '?' . http_build_query($q) : '');
    };
    $html = '<div class="pager"><span>Showing ' . $from . '–' . $to . ' of ' . $total . '</span>';
    if ($pages > 1) {
        $html .= '<span class="pager-links">';
        $html .= $page > 1 ? '<a href="' . e($url($page - 1)) . '">&larr; Previous</a>' : '<span class="disabled">&larr; Previous</span>';
        $html .= '<span>Page ' . $page . ' of ' . $pages . '</span>';
        $html .= $page < $pages ? '<a href="' . e($url($page + 1)) . '">Next &rarr;</a>' : '<span class="disabled">Next &rarr;</span>';
        $html .= '</span>';
    }
    return $html . '</div>';
}

/* ---------- Flash messages ---------- */

function flash_set(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Safe POST buttons (Suspend, Delete, Approve...) ---------- */

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid($t): bool {
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

function action_form(string $act, int $id, string $label, string $class = '', string $confirm = ''): string {
    $qs     = $_SERVER['QUERY_STRING'] ?? '';
    $return = basename($_SERVER['PHP_SELF']) . ($qs !== '' ? '?' . $qs : '');
    return '<form method="post" action="action.php" class="inline"'
         . ($confirm !== '' ? ' data-confirm="' . e($confirm) . '"' : '') . '>'
         . '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
         . '<input type="hidden" name="id" value="' . $id . '">'
         . '<input type="hidden" name="act" value="' . e($act) . '">'
         . '<input type="hidden" name="return" value="' . e($return) . '">'
         . '<button type="submit" class="btn btn-sm ' . e($class) . '">' . e($label) . '</button>'
         . '</form>';
}

function pct($value, $max): int {
    return $max > 0 ? (int)round($value / $max * 100) : 0;
}
