<?php
// helpers/auth.php

// Pastikan session dimulai jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk memeriksa apakah user sudah login.
 * Jika belum, akan otomatis diarahkan ke halaman login.
 */
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: /inventory-app/public/login.php");
        exit;
    }
}

/**
 * Fungsi untuk membatasi akses berdasarkan role.
 * Jika role tidak sesuai, tampilkan error 403 Forbidden.
 */
function requireRole($role) {
    requireLogin(); // Cek login dulu
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        exit("Akses Ditolak: Anda tidak memiliki izin untuk halaman ini.");
    }
}

/**
 * Fungsi untuk mengecek role admin secara boolean (true/false)
 */
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}
?>