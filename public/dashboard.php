<?php
// public/dashboard.php

// Panggil database
require_once __DIR__ . '/../config/database.php';

// Panggil header (Otomatis mengecek login karena di dalam header ada requireLogin)
require_once __DIR__ . '/../views/header.php';

// === QUERY STATISTIK DASHBOARD ===

// 1. Hitung Total Produk
$stmtTotal = $pdo->query("SELECT COUNT(id) as total FROM products");
$totalProducts = $stmtTotal->fetch()['total'];

// 2. Hitung Produk Stok Rendah (Stok <= Minimum Stok)
$stmtLow = $pdo->query("SELECT COUNT(id) as total FROM products WHERE stock <= minimum_stock");
$lowStock = $stmtLow->fetch()['total'];

// 3. Hitung Total Barang Masuk (IN)
$stmtIn = $pdo->query("SELECT SUM(quantity) as total FROM stock_movements WHERE type = 'IN'");
$totalIn = $stmtIn->fetch()['total'] ?? 0;

// 4. Hitung Total Barang Keluar (OUT)
$stmtOut = $pdo->query("SELECT SUM(quantity) as total FROM stock_movements WHERE type = 'OUT'");
$totalOut = $stmtOut->fetch()['total'] ?? 0;
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Dashboard Ringkasan</h2>
</div>

<!-- Widget Statistik -->
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <div class="card" style="flex: 1; min-width: 200px; border-top: 4px solid #3b82f6;">
        <h3 style="margin-top: 0; color: #6b7280; font-size: 16px;">Total Produk</h3>
        <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #1f2937;"><?= $totalProducts ?></p>
    </div>
    
    <div class="card" style="flex: 1; min-width: 200px; border-top: 4px solid #ef4444;">
        <h3 style="margin-top: 0; color: #6b7280; font-size: 16px;">Stok Menipis / Habis</h3>
        <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #ef4444;"><?= $lowStock ?></p>
    </div>
    
    <div class="card" style="flex: 1; min-width: 200px; border-top: 4px solid #10b981;">
        <h3 style="margin-top: 0; color: #6b7280; font-size: 16px;">Total Barang Masuk</h3>
        <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #1f2937;"><?= $totalIn ?></p>
    </div>
    
    <div class="card" style="flex: 1; min-width: 200px; border-top: 4px solid #f59e0b;">
        <h3 style="margin-top: 0; color: #6b7280; font-size: 16px;">Total Barang Keluar</h3>
        <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #1f2937;"><?= $totalOut ?></p>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Panduan Sistem</h3>
    <p>Selamat datang di sistem manajemen inventaris. Aplikasi ini memungkinkan Anda untuk mengelola master data produk dan melacak pergerakan stok (Barang Masuk & Keluar).</p>
    <ul>
        <li>Gunakan menu <strong>Data Produk</strong> untuk menambah, mengubah, atau menghapus master data produk.</li>
        <li>Gunakan menu <strong>Transaksi Stok</strong> untuk mencatat setiap barang yang masuk ke gudang atau keluar dari gudang.</li>
    </ul>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/../views/footer.php';
?>