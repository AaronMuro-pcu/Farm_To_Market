<?php
require_once __DIR__ . '/includes/auth.php';

$pdo   = db();
$stats = member_stats($pdo);
$online_total = $stats['farmer']['online'] + $stats['buyer']['online'];

$role   = in_array($_GET['role'] ?? '', ['farmer', 'buyer'], true) ? $_GET['role'] : '';
$action = in_array($_GET['action'] ?? '', ['login', 'logout'], true) ? $_GET['action'] : '';
$q      = trim((string)($_GET['q'] ?? ''));

// Top cards
$c = $pdo->prepare("SELECT COUNT(*) FROM login_activity l JOIN users u ON u.id = l.user_id
                    WHERE l.action = 'login' AND l.created_at >= ? AND u.role IN ('farmer','buyer')");
$c->execute([today_start()]);
$logins_today = (int)$c->fetchColumn();
$c->execute([date('Y-m-d H:i:s', strtotime('-7 days'))]);
$logins_week = (int)$c->fetchColumn();

// List
$where = ["u.role IN ('farmer','buyer')"];
$args  = [];
if ($role !== '')   { $where[] = 'u.role = ?';     $args[] = $role; }
if ($action !== '') { $where[] = 'l.action = ?';   $args[] = $action; }
if ($q !== '') {
    $where[] = "(u.name LIKE ? ESCAPE '!' OR u.email LIKE ? ESCAPE '!')";
    $args[] = $args[] = like($q);
}
$sqlWhere = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) FROM login_activity l JOIN users u ON u.id = l.user_id WHERE $sqlWhere");
$count->execute($args);
$total = (int)$count->fetchColumn();
[$page, $pages, $offset] = paginate($total, (int)($_GET['page'] ?? 1));

$list = $pdo->prepare("SELECT l.created_at, l.action, u.name, u.role
                       FROM login_activity l JOIN users u ON u.id = l.user_id
                       WHERE $sqlWhere
                       ORDER BY l.created_at DESC, l.id DESC
                       LIMIT " . PER_PAGE . " OFFSET " . (int)$offset);
$list->execute($args);
$rows = $list->fetchAll();

$pageTitle = 'Login Activity';
$pageSub   = 'Who logged in and out, and when';
$active    = 'activity';
include __DIR__ . '/includes/layout_top.php';
?>

        <section class="stats">
            <div class="stat"><div class="label">Logins today</div><div class="value"><?php echo $logins_today; ?></div></div>
            <div class="stat"><div class="label">Online now</div><div class="value"><?php echo $online_total; ?></div><div class="hint"><?php echo $stats['farmer']['online']; ?> farmers · <?php echo $stats['buyer']['online']; ?> buyers</div></div>
            <div class="stat"><div class="label">Logins, last 7 days</div><div class="value"><?php echo $logins_week; ?></div></div>
        </section>

        <section class="panel">
            <form class="filters" method="get" action="activity.php">
                <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search name or email" aria-label="Search activity">
                <select name="role" data-autosubmit aria-label="Filter by role">
                    <option value="">Farmers and buyers</option>
                    <option value="farmer" <?php echo $role === 'farmer' ? 'selected' : ''; ?>>Farmers</option>
                    <option value="buyer" <?php echo $role === 'buyer' ? 'selected' : ''; ?>>Buyers</option>
                </select>
                <select name="action" data-autosubmit aria-label="Filter by action">
                    <option value="">Logins and logouts</option>
                    <option value="login" <?php echo $action === 'login' ? 'selected' : ''; ?>>Logins</option>
                    <option value="logout" <?php echo $action === 'logout' ? 'selected' : ''; ?>>Logouts</option>
                </select>
                <button type="submit" class="btn">Search</button>
                <?php if ($q !== '' || $role !== '' || $action !== ''): ?><a href="activity.php" class="btn btn-ghost">Clear</a><?php endif; ?>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>When</th><th>Name</th><th>Role</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="4" class="empty">No activity matches your search.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="muted"><?php echo e(stamp($r['created_at'])); ?></td>
                            <td class="strong"><?php echo e($r['name']); ?></td>
                            <td><?php echo role_badge($r['role']); ?></td>
                            <td><span class="badge badge-<?php echo $r['action'] === 'login' ? 'ok' : 'info'; ?>"><?php echo e(ucfirst($r['action'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php echo pager($total, $page, $pages, 'activity.php', ['q' => $q, 'role' => $role, 'action' => $action]); ?>
        </section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
