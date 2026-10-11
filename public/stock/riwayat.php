<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
include __DIR__ . '/../../views/header.php';

// Ambil data histori pergerakan stok digabung dengan produk dan user
$sql = "SELECT sm.*, p.name AS product_name, p.sku, u.username 
        FROM stock_movements sm 
        JOIN products p ON p.id = sm.product_id 
        JOIN users u ON u.id = sm.user_id 
        ORDER BY sm.created_at DESC";
$movements = $pdo->query($sql)->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Histori Pergerakan Stok</h2>
        <p class="text-muted">Catatan audit seluruh transaksi barang masuk dan keluar.</p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Tipe</th>
                        <th>SKU & Produk</th>
                        <th>Jumlah</th>
                        <th>Petugas</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($movements) > 0): ?>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td><?= e($m['created_at']) ?></td>
                                <td>
                                    <?php if ($m['type'] === 'IN'): ?>
                                        <span class="badge bg-success">BARANG MASUK </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">BARANG KELUAR </span>
                                    <?php endif; ?>
                                </td>
                                <td>[<?= e($m['sku']) ?>] <?= e($m['product_name']) ?></td>
                                <td><strong><?= e($m['quantity']) ?></strong></td>
                                <td class="text-muted"><?= e($m['username']) ?></td>
                                <td><?= e($m['reference_note'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada histori transaksi stok.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>