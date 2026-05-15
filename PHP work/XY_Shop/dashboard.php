<?php
// ============================================================
// dashboard.php — Main Dashboard
// ============================================================
require 'security.php';
require 'auth.php';
require 'csrf.php';
require 'db.php';
require 'logger.php';

// Quick stats
$totalProducts      = $pdo->query('SELECT COUNT(*) FROM Product')->fetchColumn();
$totalStockInValue  = $pdo->query('SELECT COALESCE(SUM(TotalPrice),0) FROM ProductIn')->fetchColumn();
$totalStockOutValue = $pdo->query('SELECT COALESCE(SUM(TotalPrice),0) FROM ProductOut')->fetchColumn();
$totalStockInQty    = $pdo->query('SELECT COALESCE(SUM(Quantity),0) FROM ProductIn')->fetchColumn();
$totalStockOutQty   = $pdo->query('SELECT COALESCE(SUM(Quantity),0) FROM ProductOut')->fetchColumn();

// Recent transactions (last 5)
$recentIn  = $pdo->query('SELECT pi.*, p.ProductName FROM ProductIn pi JOIN Product p ON pi.ProductCode=p.ProductCode ORDER BY pi.DateTime DESC LIMIT 5')->fetchAll();
$recentOut = $pdo->query('SELECT po.*, p.ProductName FROM ProductOut po JOIN Product p ON po.ProductCode=p.ProductCode ORDER BY po.DateTime DESC LIMIT 5')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — XY Shop</title>
    <meta name="description" content="XY Shop Stock Management Dashboard — overview of stock activity and totals.">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <?php include 'partials/sidebar.php'; ?>
    <div class="main-content">
        <?php include 'partials/topbar.php'; ?>
        <div class="page-content">

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon purple"><i class='bx bx-package' style="font-size:1.6rem;"></i></div>
                    <div>
                        <div class="stat-value"><?= number_format($totalProducts) ?></div>
                        <div class="stat-label">Total Products</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class='bx bx-import' style="font-size:1.6rem;"></i></div>
                    <div>
                        <div class="stat-value" style="color:#10b981;"><?= number_format($totalStockInQty) ?></div>
                        <div class="stat-label">Total Units In</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class='bx bx-export' style="font-size:1.6rem;"></i></div>
                    <div>
                        <div class="stat-value" style="color:#ef4444;"><?= number_format($totalStockOutQty) ?></div>
                        <div class="stat-label">Total Units Out</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class='bx bx-wallet' style="font-size:1.6rem;"></i></div>
                    <div>
                        <div class="stat-value" style="font-size:1.2rem;color:#f59e0b;">
                            <?= number_format($totalStockInValue - $totalStockOutValue, 2) ?> RWF
                        </div>
                        <div class="stat-label">Net Stock Value</div>
                    </div>
                </div>
            </div>

            <!-- Two-column recent activity -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

                <!-- Recent Stock In -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title"><i class='bx bx-import'></i> Recent Stock-In</span>
                        <a href="stock_in.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="table-wrap">
                        <?php if ($recentIn): ?>
                        <table>
                            <thead><tr>
                                <th>Product</th><th>Qty</th><th>Total (RWF)</th><th>Date</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($recentIn as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['ProductName']) ?></strong></td>
                                    <td><span class="badge badge-green"><?= $r['Quantity'] ?></span></td>
                                    <td><?= number_format($r['TotalPrice'], 2) ?></td>
                                    <td style="font-size:0.78rem;color:var(--text-muted);">
                                        <?= date('d M, H:i', strtotime($r['DateTime'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class='bx bx-import' style="font-size:3rem;opacity:0.4;"></i></div>
                                <h3>No stock-in records yet</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Stock Out -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title"><i class='bx bx-export'></i> Recent Stock-Out</span>
                        <a href="stock_out.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="table-wrap">
                        <?php if ($recentOut): ?>
                        <table>
                            <thead><tr>
                                <th>Product</th><th>Qty</th><th>Total (RWF)</th><th>Date</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($recentOut as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['ProductName']) ?></strong></td>
                                    <td><span class="badge badge-red"><?= $r['Quantity'] ?></span></td>
                                    <td><?= number_format($r['TotalPrice'], 2) ?></td>
                                    <td style="font-size:0.78rem;color:var(--text-muted);">
                                        <?= date('d M, H:i', strtotime($r['DateTime'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class='bx bx-export' style="font-size:3rem;opacity:0.4;"></i></div>
                                <h3>No stock-out records yet</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /grid -->
        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div>
<script src="js/main.js"></script>
</body>
</html>
