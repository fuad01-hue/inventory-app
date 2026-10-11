<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

// Proteksi Admin
if (current_user()['role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

include __DIR__ . '/../../views/header.php';

$error = '';
$success = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<script>window.location.href = 'tampilan.php';</script>";
    exit;
}

// Ambil data kategori untuk dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Ambil data semua supplier
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();

// Ambil data produk saat ini
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Produk tidak ditemukan!</div></div>";
    include __DIR__ . '/../../views/footer.php';
    exit;
}

// Ambil relasi supplier yang sudah terpilih sebelumnya beserta harganya
$stmtRel = $pdo->prepare("SELECT supplier_id, supplier_price FROM product_suppliers WHERE product_id = :product_id");
$stmtRel->execute(['product_id' => $id]);
$currentSuppliers = [];
foreach ($stmtRel->fetchAll() as $row) {
    $currentSuppliers[$row['supplier_id']] = $row['supplier_price'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id   = trim($_POST['category_id'] ?? '');
    $sku           = trim($_POST['sku'] ?? '');
    $name          = trim($_POST['name'] ?? '');
    $price         = trim($_POST['price'] ?? '');
    $minimum_stock = trim($_POST['minimum_stock'] ?? '');
    $selected_suppliers = $_POST['suppliers'] ?? [];
    $supplier_prices    = $_POST['supplier_prices'] ?? [];

    if ($category_id === '' || $sku === '' || $name === '' || $price === '' || $minimum_stock === '') {
        $error = 'Semua kolom data utama produk wajib diisi!';
    } elseif ($price < 0 || $minimum_stock < 0) {
        $error = 'Harga dan minimum stok tidak boleh bernilai negatif!';
    } else {
        try {
            // === MULAI DATABASE TRANSACTION ===
            $pdo->beginTransaction();

            // 1. Update data produk utama
            $stmtUpdate = $pdo->prepare("UPDATE products SET category_id = :category_id, sku = :sku, name = :name, price = :price, minimum_stock = :minimum_stock WHERE id = :id");
            $stmtUpdate->execute([
                'category_id'   => $category_id,
                'sku'           => $sku,
                'name'          => $name,
                'price'         => $price,
                'minimum_stock' => $minimum_stock,
                'id'            => $id
            ]);
            
            // 2. Hapus relasi lama di tabel pivot
            $stmtDel = $pdo->prepare("DELETE FROM product_suppliers WHERE product_id = :product_id");
            $stmtDel->execute(['product_id' => $id]);

            // 3. Masukkan kembali relasi baru yang dicentang
            if (!empty($selected_suppliers)) {
                $stmtPivot = $pdo->prepare("INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (:product_id, :supplier_id, :supplier_price)");
                
                foreach ($selected_suppliers as $sup_id) {
                    $sup_price = !empty($supplier_prices[$sup_id]) ? $supplier_prices[$sup_id] : 0;
                    
                    $stmtPivot->execute([
                        'product_id'     => $id,
                        'supplier_id'    => $sup_id,
                        'supplier_price' => $sup_price
                    ]);
                }
            }

            // === COMMIT TRANSACTION ===
            $pdo->commit();
            
            $success = 'Data produk dan relasi supplier berhasil diperbarui!';
            
            // Perbarui data produk di memori form
            $product['category_id']   = $category_id;
            $product['sku']           = $sku;
            $product['name']          = $name;
            $product['price']         = $price;
            $product['minimum_stock'] = $minimum_stock;
            
            // Perbarui data relasi di memori form
            $currentSuppliers = [];
            foreach ($selected_suppliers as $sup_id) {
                $currentSuppliers[$sup_id] = $supplier_prices[$sup_id] ?? 0;
            }

        } catch (PDOException $e) {
            // === ROLLBACK JIKA ADA ERROR ===
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $error = 'Gagal: SKU tersebut sudah digunakan oleh produk lain.';
            } else {
                $error = 'Terjadi kesalahan pada database: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Edit Produk & Relasi</h2>
        <p class="text-muted">Perbarui detail informasi produk dan supplier pemasoknya.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="tampilan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <h5 class="mb-3 text-primary border-bottom pb-2">Data Utama Produk</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kategori Produk</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="<?= e($product['sku']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga Jual (Rp)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= e($product['price']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Minimum Stok (Peringatan)</label>
                    <input type="number" name="minimum_stock" class="form-control" value="<?= e($product['minimum_stock']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Stok Saat Ini (Hanya bisa diubah via form Barang Masuk/Keluar)</label>
                <input type="number" class="form-control bg-light" value="<?= e($product['stock']) ?>" disabled>
            </div>

            <h5 class="mb-3 mt-4 text-success border-bottom pb-2">Data Supplier (Many-to-Many)</h5>
            <p class="text-muted small">Centang supplier yang memasok produk ini dan sesuaikan harga belinya.</p>
            
            <div class="mb-4">
                <?php foreach ($suppliers as $sup): ?>
                    <?php 
                        $isChecked = array_key_exists($sup['id'], $currentSuppliers);
                        $savedPrice = $isChecked ? $currentSuppliers[$sup['id']] : '';
                    ?>
                    <div class="input-group mb-2">
                        <div class="input-group-text bg-light">
                            <input class="form-check-input mt-0" type="checkbox" name="suppliers[]" value="<?= $sup['id'] ?>" <?= $isChecked ? 'checked' : '' ?> aria-label="Checkbox supplier">
                        </div>
                        <input type="text" class="form-control bg-white" value="<?= e($sup['name']) ?>" readonly>
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="0.01" class="form-control" name="supplier_prices[<?= $sup['id'] ?>]" value="<?= e($savedPrice) ?>" placeholder="Harga Beli">
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>