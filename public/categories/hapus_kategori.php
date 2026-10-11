<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php'); // Sesuaikan dengan nama file daftar kategori Anda (misal: index.php atau daftar_kategori.php)
    exit;
}

try {
    // Coba hapus kategori berdasarkan ID
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header('Location: kategori.php?pesan=sukses_hapus');
    exit;
} catch (PDOException $e) {
    $error_message = urlencode("Kategori tidak dapat dihapus karena masih digunakan oleh produk lain!");
    header("Location: kategori.php?pesan=error&msg={$error_message}");
    exit;
}