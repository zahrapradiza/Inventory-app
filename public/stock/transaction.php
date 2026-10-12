<?php
// public/stock/transaction.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';

// Menentukan tipe transaksi dari URL (?type=IN atau ?type=OUT)
$type = $_GET['type'] ?? '';
if ($type !== 'IN' && $type !== 'OUT') {
    header("Location: index.php");
    exit;
}

$errors = [];
$success = '';
$product_id = '';
$quantity = '';
$notes = '';

// Ambil daftar produk untuk dropdown
$products = $pdo->query("SELECT id, sku, name, stock FROM products ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $notes = trim($_POST['notes'] ?? '');

    // Validasi Dasar
    if (!$product_id) $errors['product_id'] = 'Pilih produk terlebih dahulu.';
    if ($quantity === false || $quantity <= 0) $errors['quantity'] = 'Jumlah harus lebih dari 0.';

    if (empty($errors)) {
        try {
            // ==========================================
            // MULAI DATABASE TRANSACTION!
            // ==========================================
            $pdo->beginTransaction();

            // 1. Kunci baris produk ini agar tidak diubah orang lain bersamaan (FOR UPDATE)
            $stmtLock = $pdo->prepare("SELECT stock, name FROM products WHERE id = :id FOR UPDATE");
            $stmtLock->execute(['id' => $product_id]);
            $currentProduct = $stmtLock->fetch();

            if (!$currentProduct) {
                throw new Exception("Produk tidak ditemukan.");
            }

            $currentStock = $currentProduct['stock'];
            $newStock = $currentStock;

            // 2. Hitung stok baru dan Validasi Aturan Bisnis
            if ($type === 'IN') {
                $newStock += $quantity;
            } else if ($type === 'OUT') {
                if ($quantity > $currentStock) {
                    throw new Exception("Gagal! Stok barang keluar ($quantity) melebihi stok yang tersedia ($currentStock).");
                }
                $newStock -= $quantity;
            }

            // 3. Update Stok di tabel products
            $stmtUpdate = $pdo->prepare("UPDATE products SET stock = :new_stock WHERE id = :id");
            $stmtUpdate->execute([
                'new_stock' => $newStock,
                'id' => $product_id
            ]);

            // 4. Insert histori ke tabel stock_movements
            $stmtHistori = $pdo->prepare("
                INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) 
                VALUES (:product_id, :user_id, :type, :quantity, :notes)
            ");
            $stmtHistori->execute([
                'product_id' => $product_id,
                'user_id' => $_SESSION['user']['id'], // Catat siapa yang memproses
                'type' => $type,
                'quantity' => $quantity,
                'notes' => $notes
            ]);

            // ==========================================
            // JIKA SEMUA BERHASIL, SIMPAN PERMANEN (COMMIT)
            // ==========================================
            $pdo->commit();
            $success = "Transaksi " . ($type === 'IN' ? 'Barang Masuk' : 'Barang Keluar') . " berhasil dicatat!";
            
            // Reset form
            $product_id = ''; $quantity = ''; $notes = '';

        } catch (Exception $e) {
            // ==========================================
            // JIKA ADA 1 SAJA YANG GAGAL ATAU MELANGGAR ATURAN,
            // BATALKAN SELURUH PROSES (ROLLBACK)
            // ==========================================
            $pdo->rollBack();
            $errors['db'] = $e->getMessage();
        }
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Catat <?= $type === 'IN' ? 'Barang Masuk' : 'Barang Keluar' ?></h2>
    <a href="index.php" class="btn" style="background-color: #6b7280;">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 600px;">
    <?php if (!empty($errors['db'])): ?>
        <div style="background-color: #fef2f2; color: #dc2626; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #fecaca; font-weight: bold;">
            <?= htmlspecialchars($errors['db']) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div style="background-color: #ecfdf5; color: #065f46; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #a7f3d0; font-weight: bold;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="transaction.php?type=<?= $type ?>">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Pilih Produk</label>
            <select name="product_id" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px;">
                <option value="">-- Pilih --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $product_id == $p['id'] ? 'selected' : '' ?>>
                        [<?= htmlspecialchars($p['sku']) ?>] <?= htmlspecialchars($p['name']) ?> 
                        (Stok saat ini: <?= $p['stock'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['product_id'])): ?>
                <span style="color: #dc2626; font-size: 12px;"><?= $errors['product_id'] ?></span>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Jumlah (Quantity)</label>
            <input type="number" name="quantity" value="<?= htmlspecialchars($quantity) ?>" min="1" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
            <?php if (isset($errors['quantity'])): ?>
                <span style="color: #dc2626; font-size: 12px;"><?= $errors['quantity'] ?></span>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Catatan / Referensi (Opsional)</label>
            <textarea name="notes" rows="3" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;"><?= htmlspecialchars($notes) ?></textarea>
        </div>

        <button type="submit" class="btn" style="width: 100%; padding: 12px; font-size: 16px; background-color: <?= $type === 'IN' ? '#10b981' : '#ef4444' ?>;">
            Proses <?= $type === 'IN' ? 'Barang Masuk' : 'Barang Keluar' ?>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>