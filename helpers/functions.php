<?php
/**
 * Processing Layer: Berfungsi sebagai pusat logika bisnis dan kalkulasi.
 */

// Fungsi untuk menghitung total nilai aset stok barang (Harga * Stok)
function hitungTotalNilaiStok($harga, $stok) {
    return $harga * $stok;
}

// Fungsi untuk mengecek apakah stok berada dalam status kritis / di bawah minimum
function cekStokKritis($stok, $minimum_stock) {
    return $stok <= $minimum_stock;
}

// Fungsi helper untuk format Rupiah agar konsisten di seluruh tampilan
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}