<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';
require_login();
$currentUser = current_user();
?>
<?php if (current_user()['role'] === 'admin'): ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="/inventory-app/public/dashboard.php"><i class="bi bi-boxes"></i> Inventory App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory-app/public/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory-app/public/products/tampilan.php">Produk & Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory-app/public/stock/in.php">Barang Masuk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory-app/public/stock/out.php">Barang Keluar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory-app/public/categories/kategori.php">Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory-app/public/stock/riwayat.php">Histori Stok</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory-app/public/suppliers/tampilan.php">Supplier</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($currentUser['username']) ?> (<?= e(ucfirst($currentUser['role'])) ?>)
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-danger" href="/inventory-app/public/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-4">
<?php endif; ?>