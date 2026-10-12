<?php
// views/header.php
require_once __DIR__ . '/../helpers/auth.php';

// Lindungi halaman, tendang ke halaman login jika belum login
requireLogin(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <style>
        /* Styling dasar yang akan dipakai di seluruh halaman admin */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; color: #374151; }
        .navbar { background-color: #1e3a8a; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; transition: background 0.2s; font-size: 14px; }
        .navbar a:hover { background-color: #1e40af; }
        .nav-brand { font-size: 1.2rem; font-weight: bold; }
        .nav-menu { display: flex; align-items: center; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .badge-role { background-color: #f59e0b; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .btn { display: inline-block; padding: 8px 14px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background-color: #1d4ed8; }
        .btn-danger { background-color: #dc2626; }
        .btn-danger:hover { background-color: #b91c1c; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; font-size: 14px; }
        th { background-color: #f9fafb; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">📦 InventoryApp</div>
        <div class="nav-menu">
            <a href="/inventory-app/public/dashboard.php">Dashboard</a>
            <a href="/inventory-app/public/products/index.php">Data Produk</a>
            <a href="/inventory-app/public/stock/index.php">Transaksi Stok</a>
            
            <span style="margin: 0 20px; border-left: 1px solid #3b82f6; padding-left: 20px;">
                Halo, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong> 
                <span class="badge-role"><?= htmlspecialchars($_SESSION['user']['role']) ?></span>
            </span>
            <a href="/inventory-app/public/logout.php" style="background-color: #dc2626; font-weight: bold;">Logout</a>
        </div>
    </nav>
    <div class="container">