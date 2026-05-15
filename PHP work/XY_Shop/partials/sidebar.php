<?php
// partials/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <div class="brand-icon"><i class='bx bx-store-alt' style="font-size:22px;color:#fff;"></i></div>
            <div>
                <div class="brand-text">XY Shop</div>
                <div class="brand-sub">Stock Management</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="dashboard.php" class="nav-item <?= $currentPage==='dashboard.php'?'active':'' ?>">
            <span class="nav-icon"><i class='bx bx-tachometer'></i></span> Dashboard
        </a>

        <div class="nav-label">Products</div>
        <a href="products.php" class="nav-item <?= $currentPage==='products.php'?'active':'' ?>">
            <span class="nav-icon"><i class='bx bx-package'></i></span> Products
        </a>

        <div class="nav-label">Transactions</div>
        <a href="stock_in.php" class="nav-item <?= $currentPage==='stock_in.php'?'active':'' ?>">
            <span class="nav-icon"><i class='bx bx-import'></i></span> Stock In
        </a>
        <a href="stock_out.php" class="nav-item <?= $currentPage==='stock_out.php'?'active':'' ?>">
            <span class="nav-icon"><i class='bx bx-export'></i></span> Stock Out
        </a>

        <div class="nav-label">Analytics</div>
        <a href="reports.php" class="nav-item <?= $currentPage==='reports.php'?'active':'' ?>">
            <span class="nav-icon"><i class='bx bx-bar-chart-alt-2'></i></span> Reports
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['shopkeeper_name'] ?? 'S', 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($_SESSION['shopkeeper_name'] ?? 'Shopkeeper') ?></div>
                <div class="user-role">Shopkeeper</div>
            </div>
        </div>
        <a href="logout.php" class="btn btn-outline btn-sm btn-block" style="margin-top:10px;justify-content:center;">
            <i class='bx bx-log-out'></i> Logout
        </a>
    </div>
</aside>
