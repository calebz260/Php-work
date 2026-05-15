<?php
// ============================================================
// stock_in.php — Record Stock-In Transactions
// ============================================================
require 'security.php';
require 'auth.php';
require 'csrf.php';
require 'db.php';
require 'logger.php';
$uid = $_SESSION['shopkeeper_id'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $productCode = (int)($_POST['ProductCode'] ?? 0);
    $dateTime    = trim($_POST['DateTime']     ?? '');
    $quantity    = (int)($_POST['Quantity']    ?? 0);
    $unitPrice   = (float)($_POST['UnitPrice'] ?? 0);

    if ($productCode <= 0 || $dateTime === '' || $quantity <= 0 || $unitPrice <= 0) {
        $error = 'All fields are required and must be valid positive values.';
    } else {
        $totalPrice = $quantity * $unitPrice;
        $pdo->prepare(
            'INSERT INTO ProductIn (ProductCode, DateTime, Quantity, UnitPrice, TotalPrice) VALUES (?,?,?,?,?)'
        )->execute([$productCode, $dateTime, $quantity, $unitPrice, $totalPrice]);
        audit_log($pdo, 'STOCK_IN', "Stock-in: product #{$productCode}, qty={$quantity}, unit={$unitPrice}, total={$totalPrice}.", $uid);
        $success = 'Stock-in record saved successfully.';
    }
}

$products = $pdo->query('SELECT * FROM Product ORDER BY ProductName')->fetchAll();
$stockIns = $pdo->query(
    'SELECT pi.*, p.ProductName FROM ProductIn pi
     JOIN Product p ON pi.ProductCode = p.ProductCode
     ORDER BY pi.DateTime DESC LIMIT 50'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In — XY Shop</title>
    <meta name="description" content="Record incoming stock transactions for XY Shop.">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <?php include 'partials/sidebar.php'; ?>
    <div class="main-content">
        <?php include 'partials/topbar.php'; ?>
        <div class="page-content">

            <?php if ($success): ?><div class="alert alert-success"><i class='bx bx-check-circle'></i> <?= $success ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-danger"><i class='bx bx-error-circle'></i> <?= $error ?></div><?php endif; ?>

            <!-- Form Card -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <span class="card-title"><i class='bx bx-import'></i> Record Stock-In</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="stock_in.php" id="stockInForm">
                        <?= csrf_field() ?>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                            <div class="form-group">
                                <label for="ProductCode">Product</label>
                                <select id="ProductCode" name="ProductCode" required>
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['ProductCode'] ?>">
                                            <?= htmlspecialchars($p['ProductName']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="DateTime">Date &amp; Time</label>
                                <input type="datetime-local" id="DateTime" name="DateTime" required>
                            </div>
                            <div class="form-group">
                                <label for="Quantity">Quantity</label>
                                <input type="number" id="Quantity" name="Quantity"
                                       placeholder="e.g. 10" min="1" required>
                            </div>
                            <div class="form-group">
                                <label for="UnitPrice">Unit Price (RWF)</label>
                                <input type="number" id="UnitPrice" name="UnitPrice"
                                       placeholder="e.g. 5000" min="0" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="TotalPrice">Total Price (RWF)</label>
                                <input type="number" id="TotalPrice" name="TotalPrice"
                                       placeholder="Auto-calculated" readonly
                                       style="background:rgba(99,102,241,0.08);cursor:not-allowed;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success" id="saveStockInBtn">
                            <i class='bx bx-save'></i> Save Stock-In Record
                        </button>
                    </form>
                </div>
            </div>

            <!-- Records Table -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class='bx bx-list-ul'></i> Stock-In Records (Recent 50)</span>
                    <span style="font-size:0.8rem;color:var(--text-muted);">
                        <?= count($stockIns) ?> records
                    </span>
                </div>
                <div class="table-wrap">
                    <?php if ($stockIns): ?>
                    <table>
                        <thead><tr>
                            <th>#</th><th>Product</th><th>Date &amp; Time</th>
                            <th>Qty</th><th>Unit Price</th><th>Total Price</th>
                        </tr></thead>
                        <tbody>
                        <?php $i = 1; foreach ($stockIns as $s): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($s['ProductName']) ?></strong></td>
                                <td style="font-size:0.82rem;">
                                    <?= date('d M Y, H:i', strtotime($s['DateTime'])) ?>
                                </td>
                                <td><span class="badge badge-green"><?= $s['Quantity'] ?></span></td>
                                <td><?= number_format($s['UnitPrice'], 2) ?> RWF</td>
                                <td><strong style="color:#10b981;"><?= number_format($s['TotalPrice'], 2) ?> RWF</strong></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class='bx bx-import' style="font-size:3rem;opacity:0.4;"></i></div>
                            <h3>No stock-in records yet</h3>
                            <p>Use the form above to record incoming stock.</p>
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
