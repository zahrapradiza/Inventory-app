<?php
// public/products/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

// 1. Otorisasi: Hanya Admin yang boleh masuk ke halaman ini
requireRole('admin');

$errors = [];
$sku = '';
$name = '';
$category_id = '';
$price = '';
$stock = '';
$minimum_stock = 5; // Nilai default

// Ambil daftar kategori untuk dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Normalisasi & Ambil Input
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $minimum_stock = filter_input(INPUT_POST, 'minimum_stock', FILTER_VALIDATE_INT);

    // 3. Validasi (Sesuai Business Rules di Pertemuan 4)
    if (empty($sku)) {
        $errors['sku'] = 'SKU tidak boleh kosong.';
    }
    if (empty($name)) {
        $errors['name'] = 'Nama produk tidak boleh kosong.';
    }
    if (!$category_id) {
        $errors['category_id'] = 'Kategori tidak valid.';
    }
    if ($price === false || $price < 0) {
        $errors['price'] = 'Harga tidak boleh negatif.';
    }
    if ($stock === false || $stock < 0) {
        $errors['stock'] = 'Stok tidak boleh negatif.';
    }
    if ($minimum_stock === false || $minimum_stock < 0) {
        $errors['minimum_stock'] = 'Minimum stok tidak boleh negatif.';
    }

    // Cek Keunikan SKU jika belum ada error di SKU
    if (empty($errors['sku'])) {
        $stmtCek = $pdo->prepare("SELECT id FROM products WHERE sku = :sku");
        $stmtCek->execute(['sku' => $sku]);
        if ($stmtCek->fetch()) {
            $errors['sku'] = 'SKU sudah digunakan oleh produk lain.';
        }
    }

    // 4. Proses Simpan jika lolos validasi
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) 
                VALUES (:category_id, :sku, :name, :price, :stock, :minimum_stock)
            ");
            
            $stmt->execute([
                'category_id' => $category_id,
                'sku' => $sku,
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'minimum_stock' => $minimum_stock
            ]);

            // Redirect ke halaman index (PRG Pattern)
            header("Location: index.php?status=created");
            exit;
            
        } catch (PDOException $e) {
            $errors['db'] = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

// Panggil Header
require_once __DIR__ . '/../../views/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Tambah Produk Baru</h2>
    <a href="index.php" class="btn" style="background-color: #6b7280;">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 600px;">
    <?php if (!empty($errors['db'])): ?>
        <div style="background-color: #fef2f2; color: #dc2626; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #fecaca;">
            <?= htmlspecialchars($errors['db']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="create.php">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">SKU (Kode Barang Unik)</label>
            <input type="text" name="sku" value="<?= htmlspecialchars($sku) ?>" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
            <?php if (isset($errors['sku'])): ?>
                <span style="color: #dc2626; font-size: 12px;"><?= $errors['sku'] ?></span>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nama Produk</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
            <?php if (isset($errors['name'])): ?>
                <span style="color: #dc2626; font-size: 12px;"><?= $errors['name'] ?></span>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Kategori</label>
            <select name="category_id" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category_id'])): ?>
                <span style="color: #dc2626; font-size: 12px;"><?= $errors['category_id'] ?></span>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Harga (Rp)</label>
            <input type="number" name="price" value="<?= htmlspecialchars($price) ?>" min="0" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
            <?php if (isset($errors['price'])): ?>
                <span style="color: #dc2626; font-size: 12px;"><?= $errors['price'] ?></span>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Stok Awal</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>" min="0" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
                <?php if (isset($errors['stock'])): ?>
                    <span style="color: #dc2626; font-size: 12px;"><?= $errors['stock'] ?></span>
                <?php endif; ?>
            </div>
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Peringatan Stok Minimum</label>
                <input type="number" name="minimum_stock" value="<?= htmlspecialchars($minimum_stock) ?>" min="0" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
                <?php if (isset($errors['minimum_stock'])): ?>
                    <span style="color: #dc2626; font-size: 12px;"><?= $errors['minimum_stock'] ?></span>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn" style="width: 100%; padding: 10px; font-size: 16px;">Simpan Produk</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>