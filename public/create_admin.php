<?php
require_once __DIR__ . '/../config/database.php';

$username = 'admin';
$password = 'admin123'; // Password mentah
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)");
    $stmt->execute([
        'username' => $username,
        'password_hash' => $password_hash,
        'role' => $role
    ]);
    echo "Akun admin berhasil dibuat! Username: <b>admin</b> | Password: <b>admin123</b>";
} catch (PDOException $e) {
    echo "Gagal membuat akun (mungkin username sudah ada): " . $e->getMessage();
}