<?php
// Expects $pageTitle, $pageSub (optional) and $active before it is included.
$pageTitle = $pageTitle ?? 'Dashboard';
$pageSub   = $pageSub ?? '';
$active    = $active ?? 'dashboard';
$adminName = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Admin';
$pendingBadge = pending_count(db());
$flash     = flash_get();

$icons = [
    'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    'users'     => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-7 7-7s7 3 7 7"/><path d="M17 4.5a3.5 3.5 0 010 7M22 21c0-3-2-5.5-5-6.3"/>',
    'approvals' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
    'activity'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'reports'   => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
];
$nav = [
    'dashboard' => ['Dashboard',      'admin.php'],
    'users'     => ['Users',          'users.php'],
    'approvals' => ['Approvals',      'approvals.php'],
    'activity'  => ['Login Activity', 'activity.php'],
    'reports'   => ['Reports',        'reports.php'],
];
$logoFallback = "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%232d5007%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpath d=%22M5 19C5 9 11 4 20 4c0 9-5 15-15 15z%22/%3E%3Cpath d=%22M5 19c3-5 6-8 10-10%22/%3E%3C/svg%3E";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmLinked Admin | <?php echo e($pageTitle); ?></title>
    <link rel="icon" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <a href="admin.php" class="side-brand" aria-label="FarmLinked admin home">
            <img src="../assets/images/logo.png" alt="" onerror="this.onerror=null;this.src='<?php echo $logoFallback; ?>'">
            <span>FarmLinked<small>Admin</small></span>
        </a>

        <nav class="nav" aria-label="Admin">
            <?php foreach ($nav as $key => [$label, $href]): ?>
                <a href="<?php echo e($href); ?>" class="<?php echo $active === $key ? 'active' : ''; ?>"<?php echo $active === $key ? ' aria-current="page"' : ''; ?>>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><?php echo $icons[$key]; ?></svg>
                    <?php echo e($label); ?>
                    <?php if ($key === 'approvals' && $pendingBadge > 0): ?><span class="count"><?php echo $pendingBadge; ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="side-foot">
            <a href="logout.php" class="logout">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4H5v16h4M16 8l4 4-4 4M20 12H9"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="menu-btn" id="menuBtn" aria-label="Open menu">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <div>
                    <h1><?php echo e($pageTitle); ?></h1>
                    <?php if ($pageSub !== ''): ?><p><?php echo e($pageSub); ?></p><?php endif; ?>
                </div>
            </div>
            <div class="user-chip">
                <div class="avatar"><?php echo e(strtoupper(mb_substr(trim((string)$adminName) ?: 'A', 0, 1))); ?></div>
                <span class="name"><?php echo e($adminName); ?></span>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="notice notice-<?php echo e($flash['type']); ?>" role="status"><?php echo e($flash['msg']); ?></div>
        <?php endif; ?>
