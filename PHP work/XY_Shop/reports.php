<?php
// ============================================================
// reports.php — Stock Status & Value Reports
// ============================================================
require 'security.php';
require 'auth.php';
require 'csrf.php';
require 'db.php';
require 'logger.php';

$filter   = $_GET['filter']    ?? 'all';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to']   ?? '';

function buildWhere($filter, $dateFrom, $dateTo, $col = 'DateTime') {
    if ($filter === 'daily') {
        return "WHERE DATE($col) = CURDATE()";
    } elseif ($filter === 'weekly') {
        return "WHERE $col >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($filter === 'custom' && $dateFrom && $dateTo) {
        return "WHERE DATE($col) BETWEEN '" . date('Y-m-d', strtotime($dateFrom)) . "' AND '" . date('Y-m-d', strtotime($dateTo)) . "'";
    }
    return '';
}
$whereIn  = buildWhere($filter, $dateFrom, $dateTo);
$whereOut = buildWhere($filter, $dateFrom, $dateTo);

$totalInValue  = $pdo->query("SELECT COALESCE(SUM(TotalPrice),0) FROM ProductIn  $whereIn")->fetchColumn();
$totalOutValue = $pdo->query("SELECT COALESCE(SUM(TotalPrice),0) FROM ProductOut $whereOut")->fetchColumn();
$totalInQty    = $pdo->query("SELECT COALESCE(SUM(Quantity),0)   FROM ProductIn  $whereIn")->fetchColumn();
$totalOutQty   = $pdo->query("SELECT COALESCE(SUM(Quantity),0)   FROM ProductOut $whereOut")->fetchColumn();

$stockStatus = $pdo->query("
    SELECT
        p.ProductCode,
        p.ProductName,
        COALESCE(i.total_in,  0) AS TotalIn,
        COALESCE(o.total_out, 0) AS TotalOut,
        COALESCE(i.total_in,  0) - COALESCE(o.total_out, 0) AS CurrentStock,
        COALESCE(i.value_in,  0) AS ValueIn,
        COALESCE(o.value_out, 0) AS ValueOut
    FROM Product p
    LEFT JOIN (
        SELECT ProductCode, SUM(Quantity) AS total_in, SUM(TotalPrice) AS value_in
        FROM ProductIn $whereIn GROUP BY ProductCode
    ) i ON p.ProductCode = i.ProductCode
    LEFT JOIN (
        SELECT ProductCode, SUM(Quantity) AS total_out, SUM(TotalPrice) AS value_out
        FROM ProductOut $whereOut GROUP BY ProductCode
    ) o ON p.ProductCode = o.ProductCode
    ORDER BY p.ProductName
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — XY Shop</title>
    <meta name="description" content="Stock status and value reports for XY Shop inventory.">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <?php include 'partials/sidebar.php'; ?>
    <div class="main-content">
        <?php include 'partials/topbar.php'; ?>
        <div class="page-content">

            <!-- Filter Bar -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-body" style="padding:16px 24px;">
                    <form method="GET" action="reports.php" class="filter-bar" id="reportFilterForm">
                        <div class="form-group">
                            <label>Period</label>
                            <select name="filter" id="filterSelect" onchange="this.form.submit()"
                                    style="min-width:140px;">
                                <option value="all"    <?= $filter==='all'   ?'selected':'' ?>>All Time</option>
                                <option value="daily"  <?= $filter==='daily' ?'selected':'' ?>>Today</option>
                                <option value="weekly" <?= $filter==='weekly'?'selected':'' ?>>This Week (7 days)</option>
                                <option value="custom" <?= $filter==='custom'?'selected':'' ?>>Custom Range</option>
                            </select>
                        </div>
                        <?php if ($filter === 'custom'): ?>
                        <div class="form-group">
                            <label>From</label>
                            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                        </div>
                        <div class="form-group">
                            <label>To</label>
                            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                        </div>
                        <div class="form-group" style="align-self:flex-end;">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm" id="applyFilterBtn">
                                <i class='bx bx-search'></i> Apply
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Summary Boxes -->
            <div class="report-summary">
                <div class="report-box">
                    <div class="r-value" style="color:#10b981;"><?= number_format($totalInQty) ?></div>
                    <div class="r-label"><i class='bx bx-import'></i> Total Units In</div>
                </div>
                <div class="report-box">
                    <div class="r-value" style="color:#ef4444;"><?= number_format($totalOutQty) ?></div>
                    <div class="r-label"><i class='bx bx-export'></i> Total Units Out</div>
                </div>
                <div class="report-box">
                    <div class="r-value" style="color:#6366f1;font-size:1.3rem;">
                        <?= number_format($totalInValue, 2) ?> RWF
                    </div>
                    <div class="r-label"><i class='bx bx-wallet'></i> Total Value In</div>
                </div>
                <div class="report-box">
                    <div class="r-value" style="color:#f59e0b;font-size:1.3rem;">
                        <?= number_format($totalOutValue, 2) ?> RWF
                    </div>
                    <div class="r-label"><i class='bx bx-money-withdraw'></i> Total Value Out</div>
                </div>
                <div class="report-box">
                    <div class="r-value" style="color:#818cf8;font-size:1.3rem;">
                        <?= number_format($totalInValue - $totalOutValue, 2) ?> RWF
                    </div>
                    <div class="r-label"><i class='bx bx-bar-chart-alt-2'></i> Net Stock Value</div>
                </div>
            </div>

            <!-- Per-Product Report Table -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class='bx bx-line-chart'></i> Stock Status per Product</span>
                    <span style="font-size:0.8rem;color:var(--text-muted);">
                        <?= count($stockStatus) ?> products
                    </span>
                </div>
                <div class="table-wrap">
                    <?php if ($stockStatus): ?>
                    <table>
                        <thead><tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Total In (Qty)</th>
                            <th>Total Out (Qty)</th>
                            <th>Current Stock</th>
                            <th>Value In (RWF)</th>
                            <th>Value Out (RWF)</th>
                            <th>Net Value (RWF)</th>
                        </tr></thead>
                        <tbody>
                        <?php $i = 1; foreach ($stockStatus as $s): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($s['ProductName']) ?></strong></td>
                                <td><span class="badge badge-green"><?= number_format($s['TotalIn']) ?></span></td>
                                <td><span class="badge badge-red"><?= number_format($s['TotalOut']) ?></span></td>
                                <td>
                                    <?php $stock = $s['CurrentStock']; ?>
                                    <span class="badge <?= $stock > 0 ? 'badge-purple' : 'badge-orange' ?>">
                                        <?= number_format($stock) ?>
                                    </span>
                                </td>
                                <td style="color:#34d399;"><?= number_format($s['ValueIn'], 2) ?></td>
                                <td style="color:#f87171;"><?= number_format($s['ValueOut'], 2) ?></td>
                                <td>
                                    <?php $net = $s['ValueIn'] - $s['ValueOut']; ?>
                                    <strong style="color:<?= $net >= 0 ? '#818cf8' : '#f87171' ?>;">
                                        <?= number_format($net, 2) ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class='bx bx-line-chart' style="font-size:3rem;opacity:0.4;"></i></div>
                            <h3>No data available</h3>
                            <p>Add products and record transactions to generate reports.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
