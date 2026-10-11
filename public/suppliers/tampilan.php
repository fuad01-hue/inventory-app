<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if (current_user()['role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

include __DIR__ . '/../../views/header.php';

// Ambil semua data supplier
$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
$suppliers = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Manajemen Supplier</h2>
        <p class="text-muted">Kelola data pemasok barang (supplier).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Supplier</a>
    </div>
</div>

<!-- Notifikasi -->
<?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_tambah'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> Supplier berhasil ditambahkan!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_edit'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> Data supplier berhasil diperbarui!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_hapus'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> Supplier berhasil dihapus!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Supplier</th>
                        <th>Contact Person</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($suppliers) > 0): ?>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td><?= e($s['id']) ?></td>
                                <td><?= e($s['name']) ?></td>
                                <td><?= e($s['contact_person']) ?></td>
                                <td><?= e($s['phone']) ?></td>
                                <td><?= e($s['address']) ?></td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="hapus.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus supplier ini?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data supplier.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>