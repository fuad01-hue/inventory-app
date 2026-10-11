<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/escape.php';
include __DIR__ . '/../views/header.php';

// 1. Total Produk
$stmtTotalProducts = $pdo->query("SELECT COUNT(*) AS total FROM products");
$totalProducts = $stmtTotalProducts->fetch()['total'];

// 2. Nilai Inventory (SUM(stock * price))
$stmtInventoryValue = $pdo->query("SELECT SUM(stock * price) AS total_value FROM products");
$totalValue = $stmtInventoryValue->fetch()['total_value'] ?? 0;

// 3. Total Barang Masuk (IN)
$stmtTotalIn = $pdo->query("SELECT SUM(quantity) AS total_in FROM stock_movements WHERE type = 'IN'");
$totalIn = $stmtTotalIn->fetch()['total_in'] ?? 0;

// 4. Total Barang Keluar (OUT)
$stmtTotalOut = $pdo->query("SELECT SUM(quantity) AS total_out FROM stock_movements WHERE type = 'OUT'");
$totalOut = $stmtTotalOut->fetch()['total_out'] ?? 0;

// 5. Produk dengan Stok Rendah (stock <= minimum_stock)
$stmtLowStock = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.stock <= p.minimum_stock ORDER BY p.stock ASC");
$lowStockProducts = $stmtLowStock->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Dashboard Inventory</h2>
        <p class="text-muted">Ringkasan statistik dan pemantauan stok barang secara real-time.</p>
    </div>
</div>

<!-- Widget Statistik / Laporan Agregat -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-primary">
            <div class="card-body">
                <h6 class="text-muted">Total Produk</h6>
                <h3><?= number_format($totalProducts) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-success">
            <div class="card-body">
                <h6 class="text-muted">Nilai Inventory</h6>
                <h3>Rp <?= number_format($totalValue, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-info">
            <div class="card-body">
                <h6 class="text-muted">Total Barang Masuk</h6>
                <h3><?= number_format($totalIn) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-warning">
            <div class="card-body">
                <h6 class="text-muted">Total Barang Keluar</h6>
                <h3><?= number_format($totalOut) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Peringatan Stok Rendah -->
<div class="card shadow-sm">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Peringatan Produk Stok Rendah (Low Stock)</h5>
    </div>
    <div class="card-body">
        <?php if (count($lowStockProducts) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Stok Saat Ini</th>
                            <th>Minimum Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockProducts as $prod): ?>
                            <tr>
                                <td><?= e($prod['sku']) ?></td>
                                <td><?= e($prod['name']) ?></td>
                                <td><?= e($prod['category_name']) ?></td>
                                <td><span class="badge bg-danger"><?= e($prod['stock']) ?></span></td>
                                <td><?= e($prod['minimum_stock']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-success mb-0">Semua stok produk berada dalam kondisi aman.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>