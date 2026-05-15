<?php
// ============================================================
// products.php — Product Management (Add / Edit / Delete)
// ============================================================
require 'security.php';
require 'auth.php';
require 'csrf.php';
require 'db.php';
require 'logger.php';
$uid = $_SESSION['shopkeeper_id'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_verify();
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['ProductName'] ?? '');
        if ($name === '') {
            $error = 'Product name cannot be empty.';
        } else {
            $pdo->prepare('INSERT INTO Product (ProductName) VALUES (?)')->execute([$name]);
            audit_log($pdo, 'ADD_PRODUCT', "Added product: '{$name}'.", $uid);
            $success = 'Product "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" added successfully.';
        }
    } elseif ($_POST['action'] === 'edit') {
        $code = (int)($_POST['ProductCode'] ?? 0);
        $name = trim($_POST['ProductName']  ?? '');
        if ($code <= 0 || $name === '') {
            $error = 'Invalid product data.';
        } else {
            $pdo->prepare('UPDATE Product SET ProductName=? WHERE ProductCode=?')->execute([$name, $code]);
            audit_log($pdo, 'EDIT_PRODUCT', "Updated product #{$code} to: '{$name}'.", $uid);
            $success = 'Product updated successfully.';
        }
    }
}

if (isset($_GET['delete'])) {
    // Protect delete via token in URL
    $code = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM Product WHERE ProductCode=?')->execute([$code]);
    audit_log($pdo, 'DELETE_PRODUCT', "Deleted product #{$code}.", $uid);
    $success = 'Product deleted.';
}

$products = $pdo->query('SELECT * FROM Product ORDER BY ProductCode DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — XY Shop</title>
    <meta name="description" content="Manage XY Shop product catalog — add, edit, and delete products.">
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

            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class='bx bx-package'></i> Product List</span>
                    <button class="btn btn-primary btn-sm" onclick="openModal('addModal')" id="openAddProductBtn">
                        <i class='bx bx-plus'></i> Add Product
                    </button>
                </div>
                <div class="table-wrap">
                    <?php if ($products): ?>
                    <table>
                        <thead><tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Actions</th>
                        </tr></thead>
                        <tbody>
                        <?php $i = 1; foreach ($products as $p): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><span class="badge badge-purple">PC-<?= $p['ProductCode'] ?></span></td>
                                <td><strong><?= htmlspecialchars($p['ProductName']) ?></strong></td>
                                <td>
                                    <button class="btn btn-outline btn-sm"
                                            onclick="openEditProduct(<?= $p['ProductCode'] ?>, '<?= addslashes(htmlspecialchars($p['ProductName'])) ?>')"
                                            id="editBtn<?= $p['ProductCode'] ?>">
                                        <i class='bx bx-edit'></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm"
                                            onclick="confirmDelete('products.php?delete=<?= $p['ProductCode'] ?>', '<?= addslashes(htmlspecialchars($p['ProductName'])) ?>')"
                                            id="deleteBtn<?= $p['ProductCode'] ?>">
                                        <i class='bx bx-trash'></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class='bx bx-package' style="font-size:3rem;opacity:0.4;"></i></div>
                            <h3>No products found</h3>
                            <p>Click "Add Product" to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><i class='bx bx-plus-circle'></i> Add New Product</span>
            <button class="modal-close" onclick="closeModal('addModal')"><i class='bx bx-x'></i></button>
        </div>
        <form method="POST" action="products.php">
            <input type="hidden" name="action" value="add">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="ProductName">Product Name</label>
                    <input type="text" id="ProductName" name="ProductName"
                           placeholder="e.g. Nike Air Max Shoes" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveProductBtn">
                    <i class='bx bx-save'></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><i class='bx bx-edit'></i> Edit Product</span>
            <button class="modal-close" onclick="closeModal('editModal')"><i class='bx bx-x'></i></button>
        </div>
        <form method="POST" action="products.php">
            <input type="hidden" name="action" value="edit">
            <?= csrf_field() ?>
            <input type="hidden" name="ProductCode" id="edit_ProductCode">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_ProductName">Product Name</label>
                    <input type="text" id="edit_ProductName" name="ProductName"
                           placeholder="Product name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-success" id="updateProductBtn">
                    <i class='bx bx-check'></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>
