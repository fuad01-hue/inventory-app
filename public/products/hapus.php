<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: tampilan.php');
    exit;
}

try {
    // Coba hapus produk
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    // Redirect dengan parameter sukses
    header('Location: tampilan.php?pesan=sukses_hapus');
    exit;
} catch (PDOException $e) {
    // Tangkap error constraint foreign key (jika produk sudah ada di transaksi in/out)
    $error_msg = urlencode("Gagal: Produk tidak bisa dihapus karena masih memiliki riwayat barang masuk/keluar!");
    header("Location: tampilan.php?pesan=error&msg={$error_msg}");
    exit;
}