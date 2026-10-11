<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

// Proteksi Admin
if (current_user()['role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: tampilan.php');
    exit;
}

try {
    // Coba hapus supplier
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    header('Location: tampilan.php?pesan=sukses_hapus');
    exit;
} catch (PDOException $e) {
    // Tangkap error jika supplier sedang terikat relasi produk
    $error_msg = urlencode("Gagal: Supplier tidak bisa dihapus karena masih terkait dengan data produk!");
    header("Location: tampilan.php?pesan=error&msg={$error_msg}");
    exit;
}