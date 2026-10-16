<?php
require_once __DIR__ . '/includes/auth.php';

$pdo   = db();
$stats = member_stats($pdo);
$online_total = $stats['farmer']['online'] + $stats['buyer']['online'];
$pending_total = pending_count($pdo);

$pending = $pdo->query("SELECT name, produce_plan, location, created_at FROM users
                        WHERE role = 'farmer' AND status = 'pending'
                        ORDER BY created_at DESC, id DESC LIMIT 4")->fetchAll();

$q = $pdo->prepare("SELECT name, role, location, last_login FROM users
                    WHERE role IN ('farmer','buyer') AND status = 'active' AND is_online = 1 AND last_seen >= ?
                    ORDER BY last_login DESC LIMIT 8");
$q->execute([online_cutoff()]);
$online = $q->fetchAll();

$recent = $pdo->query("SELECT l.created_at, l.action, u.name, u.role
                       FROM login_activity l JOIN users u ON u.id = l.user_id
                       WHERE u.role IN ('farmer','buyer')
                       ORDER BY l.created_at DESC, l.id DESC LIMIT 6")->fetchAll();

$pageTitle = 'Dashboard';
$pageSub   = 'Overview of farmers and buyers';
$active    = 'dashboard';
include __DIR__ . '/includes/layout_top.php';
?>

        <section class="stats">
            <div class="stat"><div class="label">Farmers</div><div class="value"><?php echo $stats['farmer']['total']; ?></div><div class="hint"><?php echo $stats['farmer']['today']; ?> logged in today</div></div>
            <div class="stat"><div class="label">Buyers</div><div class="value"><?php echo $stats['buyer']['total']; ?></div><div class="hint"><?php echo $stats['buyer']['today']; ?> logged in today</div></div>
            <div class="stat"><div class="label">Online now</div><div class="value"><?php echo $online_total; ?></div><div class="hint"><?php echo $stats['farmer']['online']; ?> farmers · <?php echo $stats['buyer']['online']; ?> buyers</div></div>
            <div class="stat"><div class="label">Pending approvals</div><div class="value"><?php echo $pending_total; ?></div><div class="hint"><a href="approvals.php">Review now</a></div></div>
        </section>

        <div class="grid-2">
            <section class="panel">
                <div class="panel-head"><h2>Awaiting approval</h2><a href="approvals.php">View all</a></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Farmer</th><th>Plans to sell</th><th>Location</th><th>Requested</th></tr></thead>
                        <tbody>
                        <?php if (!$pending): ?>
                            <tr><td colspan="4" class="empty">Nothing waiting for approval.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($pending as $p): ?>
                            <tr>
                                <td class="strong"><?php echo e($p['name']); ?></td>
                                <td><?php echo e($p['produce_plan'] ?: '—'); ?></td>
                                <td><?php echo e($p['location'] ?: '—'); ?></td>
                                <td class="muted"><?php echo e(when($p['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Online now</h2><a href="users.php">All users</a></div>
                <?php if (!$online): ?>
                    <p class="muted">Nobody is online right now.</p>
                <?php else: ?>
                <ul class="list">
                    <?php foreach ($online as $o): ?>
                        <li>
                            <span><?php echo dot(true); ?><span class="strong"><?php echo e($o['name']); ?></span><br><span class="muted"><?php echo e(ucfirst($o['role'])); ?><?php echo $o['location'] ? ' · ' . e($o['location']) : ''; ?></span></span>
                            <span class="muted">since <?php echo e(clock($o['last_login'])); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>
        </div>

        <section class="panel">
            <div class="panel-head"><h2>Latest login activity</h2><a href="activity.php">View all</a></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>When</th><th>Name</th><th>Role</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$recent): ?>
                        <tr><td colspan="4" class="empty">No logins recorded yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td class="muted"><?php echo e(when($r['created_at'])); ?></td>
                            <td class="strong"><?php echo e($r['name']); ?></td>
                            <td><?php echo role_badge($r['role']); ?></td>
                            <td><?php echo e(ucfirst($r['action'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
