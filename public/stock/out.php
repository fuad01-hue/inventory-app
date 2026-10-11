<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
include __DIR__ . '/../../views/header.php';

$error = '';
$success = '';

// Ambil daftar produk untuk pilihan dropdown
$products = $pdo->query("SELECT id, sku, name, stock FROM products ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = trim($_POST['product_id'] ?? '');
    $quantity   = trim($_POST['quantity'] ?? '');
    $note       = trim($_POST['reference_note'] ?? '');
    $user_id    = current_user()['id'];

    if ($product_id === '' || $quantity === '') {
        $error = 'Produk dan jumlah barang keluar wajib diisi!';
    } elseif ((int)$quantity <= 0) {
        $error = 'Jumlah barang keluar harus lebih besar dari 0!';
    } else {
        try {
            // Mulai Database Transaction
            $pdo->beginTransaction();

            // 1. Ambil data stok terbaru dengan mengunci baris produk (FOR UPDATE)
            $stmtCheck = $pdo->prepare("SELECT stock, name FROM products WHERE id = :id FOR UPDATE");
            $stmtCheck->execute(['id' => $product_id]);
            $product = $stmtCheck->fetch();

            if (!$product) {
                throw new Exception('Produk tidak ditemukan.');
            }

            // 2. Business Rule: Barang keluar tidak boleh melebihi stok tersedia
            if ((int)$quantity > (int)$product['stock']) {
                throw new Exception("Stok tidak mencukupi! Stok saat ini untuk '{$product['name']}' hanya tersisa {$product['stock']}.");
            }

            // 3. Kurangi stok produk
            $stmtUpdate = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
            $stmtUpdate->execute([
                'qty' => $quantity,
                'id'  => $product_id
            ]);

            // 4. Catat histori ke stock_movements
            $stmtInsert = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, reference_note) VALUES (:product_id, :user_id, 'OUT', :quantity, :reference_note)");
            $stmtInsert->execute([
                'product_id'     => $product_id,
                'user_id'        => $user_id,
                'quantity'       => $quantity,
                'reference_note' => $note
            ]);

            // Commit transaksi jika semua berhasil
            $pdo->commit();
            $success = 'Transaksi barang keluar berhasil dicatat dan stok diperbarui!';
            
            // Refresh data produk terbaru untuk tampilan dropdown
            $products = $pdo->query("SELECT id, sku, name, stock FROM products ORDER BY name ASC")->fetchAll();
        } catch (Exception $e) {
            // Rollback jika terjadi kegagalan atau validasi stok gagal
            $pdo->rollBack();
            $error = 'Gagal memproses transaksi: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Transaksi Barang Keluar </h2>
        <p class="text-muted">Catat pengurangan stok barang yang keluar dari inventaris.</p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Pilih Produk</label>
                <select name="product_id" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            [<?= e($p['sku']) ?>] <?= e($p['name']) ?> (Stok Tersedia: <?= e($p['stock']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Jumlah Keluar</label>
                <input type="number" name="quantity" class="form-control" min="1" placeholder="Contoh: 5" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Catatan / Keterangan (Opsional)</label>
                <textarea name="reference_note" class="form-control" rows="2" placeholder="Contoh: Barang terjual / Digunakan untuk operasional"></textarea>
            </div>
            <button type="submit" class="btn btn-danger"><i class="bi bi-box-arrow-up-right"></i> Simpan Barang Keluar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>