<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
if (current_user()['role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}
require_once __DIR__ . '/../../helpers/escape.php';
include __DIR__ . '/../../views/header.php';

// Ambil parameter search, filter, sorting, dan pagination dari URL
$search = trim($_GET['search'] ?? '');
$category_id = $_GET['category_id'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$direction = strtoupper($_GET['direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage; // Diperbaiki di sini

// Allowlist kolom untuk sorting agar aman dari SQL Injection
$allowed_columns = ['id', 'sku', 'name', 'price', 'stock'];
if (!in_array($sort, $allowed_columns)) {
    $sort = 'id';
}

// Query dasar
$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(p.name LIKE :search OR p.sku LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if ($category_id !== '') {
    $whereClauses[] = "p.category_id = :category_id";
    $params['category_id'] = $category_id;
}

$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(' AND ', $whereClauses) : "";

// Hitung total data untuk pagination
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$stmtCount->execute($params);
$totalData = $stmtCount->fetchColumn();
$totalPages = ceil($totalData / $perPage);

// Ambil data produk dengan JOIN, sorting, limit, dan offset
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON c.id = p.category_id 
        $whereSql 
        ORDER BY p.$sort $direction 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue(':' . $key, $val);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// Ambil data kategori untuk dropdown filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<?php 
// Ambil data user yang sedang login
$userRole = current_user()['role']; 
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Manajemen Produk</h2>
        <p class="text-muted">Kelola data produk, stok, dan harga barang.</p>
    </div>
    <div class="col-md-6 text-end">
        <?php if ($userRole === 'admin'): ?>
            <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Produk</a>
            <a href="../categories/kategori.php" class="btn btn-secondary"><i class="bi bi-tags"></i> Kelola Kategori</a>
        <?php endif; ?>
    </div>
</div>

<!-- Tempat memunculkan pesan notifikasi -->
    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses_hapus'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> Produk berhasil dihapus!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_GET['msg'] ?? 'Gagal menghapus produk.') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

<!-- Form Search, Filter, & Sorting -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Cari Produk / SKU</label>
                <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Nama produk atau SKU...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Urutkan Berdasarkan</label>
                <select name="sort" class="form-select">
                    <option value="id" <?= $sort == 'id' ? 'selected' : '' ?>>ID</option>
                    <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Nama</option>
                    <option value="price" <?= $sort == 'price' ? 'selected' : '' ?>>Harga</option>
                    <option value="stock" <?= $sort == 'stock' ? 'selected' : '' ?>>Stok</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Arah Urutan</label>
                <select name="direction" class="form-select">
                    <option value="ASC" <?= $direction == 'ASC' ? 'selected' : '' ?>>Ascending (ASC)</option>
                    <option value="DESC" <?= $direction == 'DESC' ? 'selected' : '' ?>>Descending (DESC)</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Produk -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Min Stok</th>
                        <?php if ($userRole === 'admin'): ?>
                            <th class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= e($p['sku']) ?></td>
                                <td><?= e($p['name']) ?></td>
                                <td><?= e($p['category_name']) ?></td>
                                <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge <?= $p['stock'] <= $p['minimum_stock'] ? 'bg-danger' : 'bg-success' ?>">
                                        <?= e($p['stock']) ?>
                                    </span>
                                </td>
                                <td><?= e($p['minimum_stock']) ?></td>
                                <?php if ($userRole === 'admin'): ?>
                                    <td class="text-center">
                                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="hapus.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data produk ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category_id=<?= $category_id ?>&sort=<?= $sort ?>&direction=<?= $direction ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>