<?php
require_once __DIR__ . '/includes/auth.php';

$pdo = db();
$q   = trim((string)($_GET['q'] ?? ''));

$where = ["role = 'farmer'", "status = 'pending'"];
$args  = [];
if ($q !== '') {
    $where[] = "(name LIKE ? ESCAPE '!' OR email LIKE ? ESCAPE '!' OR location LIKE ? ESCAPE '!' OR produce_plan LIKE ? ESCAPE '!')";
    $args[] = $args[] = $args[] = $args[] = like($q);
}
$sqlWhere = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $sqlWhere");
$count->execute($args);
$total = (int)$count->fetchColumn();
[$page, $pages, $offset] = paginate($total, (int)($_GET['page'] ?? 1));

$list = $pdo->prepare("SELECT id, name, email, location, produce_plan, created_at
                       FROM users WHERE $sqlWhere
                       ORDER BY created_at ASC, id ASC
                       LIMIT " . PER_PAGE . " OFFSET " . (int)$offset);
$list->execute($args);
$rows = $list->fetchAll();

$waiting   = pending_count($pdo);
$pageTitle = 'Approvals';
$pageSub   = $waiting . ($waiting === 1 ? ' farmer' : ' farmers') . ' waiting for approval to sell';
$active    = 'approvals';
include __DIR__ . '/includes/layout_top.php';
?>

        <section class="panel">
            <form class="filters" method="get" action="approvals.php">
                <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search name, produce or location" aria-label="Search registrations">
                <button type="submit" class="btn">Search</button>
                <?php if ($q !== ''): ?><a href="approvals.php" class="btn btn-ghost">Clear</a><?php endif; ?>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Farmer</th><th>Plans to sell</th><th>Location</th><th>Requested</th><th>Decision</th></tr></thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="5" class="empty"><?php echo $q !== '' ? 'No applications match your search.' : 'Nothing waiting for approval.'; ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="strong"><?php echo e($r['name']); ?><br><span class="muted" style="font-weight:400"><?php echo e($r['email']); ?></span></td>
                            <td><?php echo e($r['produce_plan'] ?: '—'); ?></td>
                            <td><?php echo e($r['location'] ?: '—'); ?></td>
                            <td class="muted"><?php echo e(when($r['created_at'])); ?></td>
                            <td>
                                <div class="actions">
                                    <?php echo action_form('approve', (int)$r['id'], 'Approve'); ?>
                                    <?php echo action_form('reject', (int)$r['id'], 'Reject', 'btn-danger', 'Reject ' . $r['name'] . "'s application? Their registration will be removed."); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php echo pager($total, $page, $pages, 'approvals.php', ['q' => $q]); ?>
        </section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
