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

// Ambil data kategori untuk dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Ambil data supplier untuk checkbox relasi Many-to-Many
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id   = trim($_POST['category_id'] ?? '');
    $sku           = trim($_POST['sku'] ?? '');
    $name          = trim($_POST['name'] ?? '');
    $price         = trim($_POST['price'] ?? ''); // Harga Jual
    $minimum_stock = trim($_POST['minimum_stock'] ?? '');
    $selected_suppliers = $_POST['suppliers'] ?? [];
    $supplier_prices    = $_POST['supplier_prices'] ?? [];

    if ($category_id === '' || $sku === '' || $name === '' || $price === '' || $minimum_stock === '') {
        $error = 'Semua kolom data utama produk wajib diisi!';
    } elseif ($price < 0 || $minimum_stock < 0) {
        $error = 'Harga dan minimum stok tidak boleh negatif!';
    } else {
        try {
            // === MULAI DATABASE TRANSACTION ===
            $pdo->beginTransaction();

            // 1. Simpan data ke tabel products (stok awal otomatis 0, harus diisi via Barang Masuk)
            $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) VALUES (:category_id, :sku, :name, :price, 0, :minimum_stock)");
            $stmt->execute([
                'category_id'   => $category_id,
                'sku'           => $sku,
                'name'          => $name,
                'price'         => $price,
                'minimum_stock' => $minimum_stock
            ]);
            
            // Dapatkan ID produk yang baru saja dibuat
            $product_id = $pdo->lastInsertId();

            // 2. Simpan relasi ke tabel product_suppliers (Many-to-Many)
            if (!empty($selected_suppliers)) {
                $stmtPivot = $pdo->prepare("INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (:product_id, :supplier_id, :supplier_price)");
                
                foreach ($selected_suppliers as $sup_id) {
                    // Ambil harga dari input array, default 0 jika kosong
                    $sup_price = !empty($supplier_prices[$sup_id]) ? $supplier_prices[$sup_id] : 0;
                    
                    $stmtPivot->execute([
                        'product_id'     => $product_id,
                        'supplier_id'    => $sup_id,
                        'supplier_price' => $sup_price
                    ]);
                }
            }

            // === COMMIT TRANSACTION JIKA SEMUA SUKSES ===
            $pdo->commit();
            
            echo "<script>window.location.href = 'tampilan.php?pesan=sukses_tambah';</script>";
            exit;
        } catch (PDOException $e) {
            // === ROLLBACK TRANSACTION JIKA ADA ERROR ===
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $error = 'Gagal: SKU tersebut sudah digunakan oleh produk lain.';
            } else {
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Tambah Produk Baru</h2>
        <p class="text-muted">Masukkan data produk beserta supplier yang memasoknya.</p>
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

        <form method="POST">
            <h5 class="mb-3 text-primary border-bottom pb-2">Data Utama Produk</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU (Kode Barang) <span class="text-danger">*</span></label>
                    <input type="text" name="sku" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Minimum Stok Peringatan <span class="text-danger">*</span></label>
                    <input type="number" name="minimum_stock" class="form-control" required>
                </div>
            </div>

            <h5 class="mb-3 mt-4 text-success border-bottom pb-2">Data Supplier (Many-to-Many)</h5>
            <p class="text-muted small">Centang supplier yang memasok produk ini, lalu masukkan harga belinya.</p>
            
            <div class="mb-4">
                <?php foreach ($suppliers as $sup): ?>
                    <div class="input-group mb-2">
                        <div class="input-group-text bg-light">
                            <input class="form-check-input mt-0" type="checkbox" name="suppliers[]" value="<?= $sup['id'] ?>" aria-label="Checkbox for following text input">
                        </div>
                        <input type="text" class="form-control bg-white" value="<?= e($sup['name']) ?>" readonly>
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="0.01" class="form-control" name="supplier_prices[<?= $sup['id'] ?>]" placeholder="Harga Beli dari Supplier ini">
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Produk & Relasi</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>