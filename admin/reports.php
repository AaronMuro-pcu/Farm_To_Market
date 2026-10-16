<?php
require_once __DIR__ . '/includes/auth.php';

$pdo     = db();
$members = "role IN ('farmer','buyer')";

// Accounts by status
$status = ['active' => 0, 'suspended' => 0, 'pending' => 0];
foreach ($pdo->query("SELECT status, COUNT(*) AS c FROM users WHERE $members GROUP BY status") as $r) {
    $status[$r['status']] = (int)$r['c'];
}
$total_members = $status['active'] + $status['suspended'];

// Farmers vs buyers
$by_role = ['farmer' => 0, 'buyer' => 0];
foreach ($pdo->query("SELECT role, COUNT(*) AS c FROM users WHERE $members AND status <> 'pending' GROUP BY role") as $r) {
    $by_role[$r['role']] = (int)$r['c'];
}
$active_pct = $total_members > 0 ? (int)round($status['active'] / $total_members * 100) : 0;

// Sign-ups, last 6 months
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("first day of -$i month"));
    $months[$key] = ['label' => date('M Y', strtotime($key . '-01')), 'farmer' => 0, 'buyer' => 0];
}
$first = array_key_first($months) . '-01 00:00:00';
$q = $pdo->prepare("SELECT role, created_at FROM users WHERE $members AND status <> 'pending' AND created_at >= ?");
$q->execute([$first]);
foreach ($q as $r) {
    $key = substr((string)$r['created_at'], 0, 7);
    if (isset($months[$key])) $months[$key][$r['role']]++;
}
$max_month  = 1;
foreach ($months as $m) $max_month = max($max_month, $m['farmer'] + $m['buyer']);
$this_month = $months[date('Y-m')] ?? ['farmer' => 0, 'buyer' => 0];
$new_this_month = $this_month['farmer'] + $this_month['buyer'];

// Members by location (top 10)
$locations = $pdo->query("SELECT COALESCE(NULLIF(location, ''), 'Not set') AS place, COUNT(*) AS c
                          FROM users WHERE $members AND status <> 'pending'
                          GROUP BY place ORDER BY c DESC, place ASC LIMIT 10")->fetchAll();
$max_loc = 1;
foreach ($locations as $l) $max_loc = max($max_loc, (int)$l['c']);

$max_status = max(1, $status['active'], $status['suspended'], $status['pending']);

$pageTitle = 'Reports';
$pageSub   = 'Members and sign-ups at a glance';
$active    = 'reports';
include __DIR__ . '/includes/layout_top.php';
?>

        <section class="stats">
            <div class="stat"><div class="label">Total members</div><div class="value"><?php echo $total_members; ?></div><div class="hint"><?php echo $by_role['farmer']; ?> farmers · <?php echo $by_role['buyer']; ?> buyers</div></div>
            <div class="stat"><div class="label">Active accounts</div><div class="value"><?php echo $active_pct; ?>%</div><div class="hint"><?php echo $status['suspended']; ?> suspended</div></div>
            <div class="stat"><div class="label">New this month</div><div class="value"><?php echo $new_this_month; ?></div><div class="hint"><?php echo $status['pending']; ?> waiting for approval</div></div>
        </section>

        <section class="panel">
            <h2>Sign-ups, last 6 months</h2>
            <div class="bars">
                <?php foreach ($months as $m): $n = $m['farmer'] + $m['buyer']; ?>
                    <div class="bar-row">
                        <span class="name"><?php echo e($m['label']); ?></span>
                        <div class="bar-track"><div class="bar-fill" style="width: <?php echo pct($n, $max_month); ?>%"></div></div>
                        <span class="amount"><?php echo $n; ?> <span class="muted" style="font-weight:400">(<?php echo $m['farmer']; ?>F · <?php echo $m['buyer']; ?>B)</span></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="grid-2" style="grid-template-columns: 1fr 1fr;">
            <section class="panel">
                <h2>Accounts by status</h2>
                <div class="bars">
                    <div class="bar-row"><span class="name">Active</span><div class="bar-track"><div class="bar-fill" style="width: <?php echo pct($status['active'], $max_status); ?>%"></div></div><span class="amount"><?php echo $status['active']; ?></span></div>
                    <div class="bar-row"><span class="name">Suspended</span><div class="bar-track"><div class="bar-fill alt" style="width: <?php echo pct($status['suspended'], $max_status); ?>%"></div></div><span class="amount"><?php echo $status['suspended']; ?></span></div>
                    <div class="bar-row"><span class="name">Pending</span><div class="bar-track"><div class="bar-fill alt" style="width: <?php echo pct($status['pending'], $max_status); ?>%"></div></div><span class="amount"><?php echo $status['pending']; ?></span></div>
                </div>
            </section>
            <section class="panel">
                <h2>Members by location</h2>
                <div class="bars">
                    <?php if (!$locations): ?><p class="muted">No members yet.</p><?php endif; ?>
                    <?php foreach ($locations as $l): ?>
                        <div class="bar-row"><span class="name" style="font-size:.85rem"><?php echo e($l['place']); ?></span><div class="bar-track"><div class="bar-fill alt" style="width: <?php echo pct((int)$l['c'], $max_loc); ?>%"></div></div><span class="amount"><?php echo (int)$l['c']; ?></span></div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
