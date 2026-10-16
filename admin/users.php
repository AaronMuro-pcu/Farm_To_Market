<?php
require_once __DIR__ . '/includes/auth.php';

$pdo   = db();
$stats = member_stats($pdo);

$role   = in_array($_GET['role'] ?? '', ['farmer', 'buyer'], true) ? $_GET['role'] : '';
$status = in_array($_GET['status'] ?? '', ['active', 'suspended'], true) ? $_GET['status'] : '';
$q      = trim((string)($_GET['q'] ?? ''));

$where = ["role IN ('farmer','buyer')", "status <> 'pending'"];
$args  = [];
if ($role !== '')   { $where[] = 'role = ?';   $args[] = $role; }
if ($status !== '') { $where[] = 'status = ?'; $args[] = $status; }
if ($q !== '') {
    $where[] = "(name LIKE ? ESCAPE '!' OR email LIKE ? ESCAPE '!' OR location LIKE ? ESCAPE '!')";
    $args[] = $args[] = $args[] = like($q);
}
$sqlWhere = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $sqlWhere");
$count->execute($args);
$total = (int)$count->fetchColumn();
[$page, $pages, $offset] = paginate($total, (int)($_GET['page'] ?? 1));

$list = $pdo->prepare("SELECT id, name, email, role, location, status, is_online, last_login, last_seen
                       FROM users WHERE $sqlWhere
                       ORDER BY created_at DESC, id DESC
                       LIMIT " . PER_PAGE . " OFFSET " . (int)$offset);
$list->execute($args);
$users = $list->fetchAll();

$tab = function (string $r) use ($status, $q): string {
    $p = array_filter(['role' => $r, 'status' => $status, 'q' => $q], fn($v) => $v !== '');
    return 'users.php' . ($p ? '?' . http_build_query($p) : '');
};

$filtered = ($q !== '' || $status !== '');
$pageTitle = 'Users';
$pageSub   = ($stats['farmer']['total'] + $stats['buyer']['total']) . ' farmers and buyers';
$active    = 'users';
include __DIR__ . '/includes/layout_top.php';
?>

        <section class="stats">
            <div class="stat"><div class="label">Farmers</div><div class="value"><?php echo $stats['farmer']['total']; ?></div><div class="hint"><?php echo $stats['farmer']['suspended']; ?> suspended</div></div>
            <div class="stat"><div class="label">Buyers</div><div class="value"><?php echo $stats['buyer']['total']; ?></div><div class="hint"><?php echo $stats['buyer']['suspended']; ?> suspended</div></div>
            <div class="stat"><div class="label">Farmers logged in today</div><div class="value"><?php echo $stats['farmer']['today']; ?></div><div class="hint"><?php echo $stats['farmer']['online']; ?> online now</div></div>
            <div class="stat"><div class="label">Buyers logged in today</div><div class="value"><?php echo $stats['buyer']['today']; ?></div><div class="hint"><?php echo $stats['buyer']['online']; ?> online now</div></div>
        </section>

        <section class="panel">
            <div class="toolbar">
                <div class="seg">
                    <a href="<?php echo e($tab('')); ?>" class="<?php echo $role === '' ? 'active' : ''; ?>">All</a>
                    <a href="<?php echo e($tab('farmer')); ?>" class="<?php echo $role === 'farmer' ? 'active' : ''; ?>">Farmers</a>
                    <a href="<?php echo e($tab('buyer')); ?>" class="<?php echo $role === 'buyer' ? 'active' : ''; ?>">Buyers</a>
                </div>
                <form class="filters" method="get" action="users.php">
                    <input type="hidden" name="role" value="<?php echo e($role); ?>">
                    <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search name, email or location" aria-label="Search users">
                    <select name="status" data-autosubmit aria-label="Filter by status">
                        <option value="">All statuses</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="suspended" <?php echo $status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                    <button type="submit" class="btn">Search</button>
                    <?php if ($filtered): ?><a href="<?php echo e('users.php' . ($role !== '' ? '?role=' . $role : '')); ?>" class="btn btn-ghost">Clear</a><?php endif; ?>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Role</th><th>Location</th><th>Status</th><th>Last login</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$users): ?>
                        <tr><td colspan="6" class="empty">No users match your search.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($users as $u): $on = $u['status'] === 'active' && is_online($u); ?>
                        <tr>
                            <td class="strong"><?php echo dot($on); ?><?php echo e($u['name']); ?><br><span class="muted" style="font-weight:400"><?php echo e($u['email']); ?></span></td>
                            <td><?php echo role_badge($u['role']); ?></td>
                            <td><?php echo e($u['location'] ?: '—'); ?></td>
                            <td><?php echo status_badge($u['status']); ?></td>
                            <td class="muted"><?php echo $on ? 'Online now' : e($u['last_login'] ? when($u['last_login']) : 'Never'); ?></td>
                            <td>
                                <div class="actions">
                                    <?php if ($u['status'] === 'active'): ?>
                                        <?php echo action_form('toggle', (int)$u['id'], 'Suspend', 'btn-warn'); ?>
                                    <?php else: ?>
                                        <?php echo action_form('toggle', (int)$u['id'], 'Activate'); ?>
                                    <?php endif; ?>
                                    <?php echo action_form('delete', (int)$u['id'], 'Delete', 'btn-delete', 'Delete ' . $u['name'] . "'s account? This cannot be undone."); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php echo pager($total, $page, $pages, 'users.php', ['role' => $role, 'status' => $status, 'q' => $q]); ?>
        </section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
