<?php
// public/stock/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';

// Ambil riwayat transaksi, join dengan tabel produk dan user
$sql = "SELECT sm.*, p.name as product_name, p.sku, u.username 
        FROM stock_movements sm 
        JOIN products p ON sm.product_id = p.id 
        JOIN users u ON sm.user_id = u.id 
        ORDER BY sm.movement_date DESC LIMIT 50";
$movements = $pdo->query($sql)->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Riwayat Transaksi Stok</h2>
    <div>
        <a href="transaction.php?type=IN" class="btn" style="background-color: #10b981;">+ Barang Masuk</a>
        <a href="transaction.php?type=OUT" class="btn btn-danger">+ Barang Keluar</a>
    </div>
</div>

<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>SKU - Produk</th>
                    <th>Jumlah</th>
                    <th>Dicatat Oleh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movements) > 0): ?>
                    <?php foreach ($movements as $m): ?>
                        <tr>
                            <td><?= date('d M Y H:i', strtotime($m['movement_date'])) ?></td>
                            <td>
                                <?php if ($m['type'] === 'IN'): ?>
                                    <span class="badge-role" style="background-color: #10b981;">MASUK</span>
                                <?php else: ?>
                                    <span class="badge-role" style="background-color: #ef4444;">KELUAR</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($m['sku'] . ' - ' . $m['product_name']) ?></td>
                            <td><strong><?= $m['quantity'] ?></strong></td>
                            <td><?= htmlspecialchars($m['username']) ?></td>
                            <td><?= htmlspecialchars($m['notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: #6b7280;">Belum ada riwayat transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>