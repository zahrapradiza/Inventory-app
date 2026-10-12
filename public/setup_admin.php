<?php
// public/setup_admin.php
require_once __DIR__ . '/../config/database.php';

// Kita generate ulang hash untuk password "password123" langsung dari PHP
$password_asli = 'password123';
$hash_baru = password_hash($password_asli, PASSWORD_DEFAULT);

try {
    // 1. Matikan sementara aturan Foreign Key agar MySQL mengizinkan penghapusan
    $pdo->query("SET FOREIGN_KEY_CHECKS = 0");

    // 2. Kosongkan tabel users
    $pdo->query("TRUNCATE TABLE users");

    // 3. Nyalakan kembali aturan Foreign Key agar database kembali ketat dan aman
    $pdo->query("SET FOREIGN_KEY_CHECKS = 1");

    // 4. Masukkan data admin dan staff dengan hash yang baru
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:user, :hash, :role)");
    
    // Insert Admin
    $stmt->execute([
        'user' => 'admin_tes',
        'hash' => $hash_baru,
        'role' => 'admin'
    ]);

    // Insert Staff
    $stmt->execute([
        'user' => 'staff_tes',
        'hash' => $hash_baru,
        'role' => 'staff'
    ]);

    echo "<h3>SUKSES!</h3>";
    echo "<p>Akun berhasil dibuat ulang dengan aman tanpa melanggar relasi tabel.</p>";
    echo "<ul>";
    echo "<li>Username: <strong>admin_tes</strong></li>";
    echo "<li>Password: <strong>password123</strong></li>";
    echo "</ul>";
    echo "<a href='login.php'>Klik di sini untuk mencoba Login kembali</a>";

} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}
?>