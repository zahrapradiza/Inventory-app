<?php
// public/products/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

requireRole('admin'); // Hanya Admin yang bisa mengedit

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$errors = [];
// Ambil data produk saat ini
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $minimum_stock = filter_input(INPUT_POST, 'minimum_stock', FILTER_VALIDATE_INT);

    if (empty($sku)) $errors['sku'] = 'SKU tidak boleh kosong.';
    if (empty($name)) $errors['name'] = 'Nama produk tidak boleh kosong.';
    if (!$category_id) $errors['category_id'] = 'Kategori tidak valid.';
    if ($price === false || $price < 0) $errors['price'] = 'Harga tidak boleh negatif.';
    if ($minimum_stock === false || $minimum_stock < 0) $errors['minimum_stock'] = 'Minimum stok tidak boleh negatif.';

    // Cek Keunikan SKU (kecuali SKU milik produk ini sendiri)
    if (empty($errors['sku'])) {
        $stmtCek = $pdo->prepare("SELECT id FROM products WHERE sku = :sku AND id != :id");
        $stmtCek->execute(['sku' => $sku, 'id' => $id]);
        if ($stmtCek->fetch()) {
            $errors['sku'] = 'SKU sudah digunakan oleh produk lain.';
        }
    }

    if (empty($errors)) {
        try {
            // Stok tidak ikut di-update di sini!
            $stmtUpdate = $pdo->prepare("
                UPDATE products 
                SET category_id = :cat, sku = :sku, name = :name, price = :price, minimum_stock = :min_stock 
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'cat' => $category_id, 'sku' => $sku, 'name' => $name, 
                'price' => $price, 'min_stock' => $minimum_stock, 'id' => $id
            ]);
            header("Location: index.php?status=updated");
            exit;
        } catch (PDOException $e) {
            $errors['db'] = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}
require_once __DIR__ . '/../../views/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Edit Produk</h2>
    <a href="index.php" class="btn" style="background-color: #6b7280;">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="edit.php?id=<?= $id ?>">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">SKU</label>
            <input type="text" name="sku" value="<?= htmlspecialchars($_POST['sku'] ?? $product['sku']) ?>" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            <?php if (isset($errors['sku'])): ?> <span style="color: #dc2626; font-size: 12px;"><?= $errors['sku'] ?></span> <?php endif; ?>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nama Produk</label>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Kategori</label>
            <select name="category_id" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                <?php $current_cat = $_POST['category_id'] ?? $product['category_id']; ?>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $current_cat == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Harga (Rp)</label>
            <input type="number" name="price" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>" min="0" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Peringatan Stok Minimum</label>
            <input type="number" name="minimum_stock" value="<?= htmlspecialchars($_POST['minimum_stock'] ?? $product['minimum_stock']) ?>" min="0" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
        </div>

        <button type="submit" class="btn" style="width: 100%; padding: 10px; font-size: 16px; background-color: #f59e0b;">Update Produk</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../views/footer.php'; ?> 