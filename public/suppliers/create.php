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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $error = 'Nama supplier wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, address) VALUES (:name, :contact_person, :phone, :address)");
            $stmt->execute([
                'name' => $name,
                'contact_person' => $contact_person,
                'phone' => $phone,
                'address' => $address
            ]);
            
            echo "<script>window.location.href = 'tampilan.php?pesan=sukses_tambah';</script>";
            exit;
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Tambah Supplier</h2>
        <p class="text-muted">Masukkan data supplier baru.</p>
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
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Supplier</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>