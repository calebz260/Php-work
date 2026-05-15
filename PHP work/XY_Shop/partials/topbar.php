<?php
// partials/topbar.php
$pageTitles = [
    'dashboard.php' => ['Dashboard',   'Welcome back! Here\'s your stock overview.'],
    'products.php'  => ['Products',    'Manage your product catalog.'],
    'stock_in.php'  => ['Stock In',    'Record incoming stock transactions.'],
    'stock_out.php' => ['Stock Out',   'Record outgoing stock transactions.'],
    'reports.php'   => ['Reports',     'View stock status and value reports.'],
];
$page = basename($_SERVER['PHP_SELF']);
[$title, $subtitle] = $pageTitles[$page] ?? ['XY Shop', ''];
?>
<header class="topbar">
    <div class="topbar-title">
        <h2><?= $title ?></h2>
        <p><?= $subtitle ?></p>
    </div>
    <div class="topbar-actions">
        <span style="font-size:0.8rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
            <i class='bx bx-calendar' style="font-size:1rem;"></i>
            <?= date('D, d M Y') ?>
        </span>
    </div>
</header>
