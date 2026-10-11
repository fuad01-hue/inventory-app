<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
include __DIR__ . '/../../views/header.php';

$error = '';
$success = '';

// Tangkap pesan dari URL jika ada proses hapus
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'sukses_hapus') {
        $success = 'Kategori berhasil dihapus!';
    } elseif ($_GET['pesan'] === 'error' && isset($_GET['msg'])) {
        $error = $_GET['msg'];
    }
}

// Proses tambah kategori baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Nama kategori wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
            $stmt->execute(['name' => $name, 'description' => $description]);
            $success = 'Kategori baru berhasil ditambahkan!';
        } catch (PDOException $e) {
            $error = 'Gagal menambah kategori (mungkin nama sudah ada).';
        }
    }
}

// Ambil semua daftar kategori beserta total produk di dalamnya
$categories = $pdo->query("SELECT c.*, COUNT(p.id) AS total_products FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Manajemen Kategori</h2>
        <p class="text-muted">Kelola pengelompokan produk inventaris.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="../products/tampilan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Produk</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="row">
    <!-- Form Tambah Kategori -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Tambah Kategori</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Elektronik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Keterangan kategori..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Kategori</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Kategori -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Produk</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categories) > 0): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><strong><?= e($cat['name']) ?></strong></td>
                                        <td><?= e($cat['description'] ?? '-') ?></td>
                                        <td><span class="badge bg-primary"><?= e($cat['total_products']) ?> Produk</span></td>
                                        <td class="text-center">
                                            <a href="hapus_kategori.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada kategori.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>