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
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<script>window.location.href = 'tampilan.php';</script>";
    exit;
}

// Ambil data supplier saat ini
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
$stmt->execute(['id' => $id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Supplier tidak ditemukan!</div></div>";
    include __DIR__ . '/../../views/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $error = 'Nama supplier wajib diisi!';
    } else {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE suppliers SET name = :name, contact_person = :contact_person, phone = :phone, address = :address WHERE id = :id");
            $stmtUpdate->execute([
                'name' => $name,
                'contact_person' => $contact_person,
                'phone' => $phone,
                'address' => $address,
                'id' => $id
            ]);
            
            echo "<script>window.location.href = 'tampilan.php?pesan=sukses_edit';</script>";
            exit;
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan database: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Edit Supplier</h2>
        <p class="text-muted">Perbarui data informasi supplier.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="tampilan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= e($supplier['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-control" value="<?= e($supplier['contact_person']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="phone" class="form-control" value="<?= e($supplier['phone']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="3"><?= e($supplier['address']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>