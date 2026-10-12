<?php
// public/products/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php'; // Header otomatis memvalidasi login

// === FITUR FILTER, SEARCH, SORTING & PAGINATION ===
$search = trim($_GET['search'] ?? '');
$filter_category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$sort = $_GET['sort'] ?? 'name_asc';
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// --- Ambil Kategori untuk Dropdown Filter ---
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// --- Persiapkan Base Query untuk Menghitung Total dan Mengambil Data ---
$whereConditions = [];
$params = [];

if ($search !== '') {
    $whereConditions[] = "(p.name LIKE :search OR p.sku LIKE :search)";
    $params['search'] = "%$search%";
}

if ($filter_category) {
    $whereConditions[] = "p.category_id = :category";
    $params['category'] = $filter_category;
}

$whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// --- Hitung Total Data untuk Pagination ---
$countQuery = "SELECT COUNT(p.id) as total FROM products p $whereClause";
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalRows = $stmtCount->fetch()['total'];
$totalPages = ceil($totalRows / $perPage);

// --- Penanganan Sorting ---
$orderBy = "p.name ASC"; // Default
$sortOptions = [
    'name_asc' => 'Nama (A-Z)',
    'name_desc' => 'Nama (Z-A)',
    'price_asc' => 'Harga Termurah',
    'price_desc' => 'Harga Termahal',
    'stock_asc' => 'Stok Sedikit',
    'stock_desc' => 'Stok Banyak'
];

if (array_key_exists($sort, $sortOptions)) {
    switch ($sort) {
        case 'name_desc': $orderBy = "p.name DESC"; break;
        case 'price_asc': $orderBy = "p.price ASC"; break;
        case 'price_desc': $orderBy = "p.price DESC"; break;
        case 'stock_asc': $orderBy = "p.stock ASC"; break;
        case 'stock_desc': $orderBy = "p.stock DESC"; break;
    }
}

// --- Query Utama dengan JOIN untuk Mendapatkan Nama Kategori ---
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $whereClause 
        ORDER BY $orderBy 
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fungsi bantuan untuk mempertahankan parameter GET di URL saat pindah halaman (pagination)
function buildUrl($params) {
    return '?' . http_build_query(array_merge($_GET, $params));
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Data Master Produk</h2>
    <?php if (isAdmin()): ?>
        <a href="create.php" class="btn">+ Tambah Produk</a>
    <?php endif; ?>
</div>

<!-- Notifikasi Status Aksi -->
<?php $status = $_GET['status'] ?? ''; $error = $_GET['error'] ?? ''; ?>
<?php if ($status === 'created'): ?>
    <div style="background-color: #ecfdf5; color: #065f46; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #a7f3d0;">Produk berhasil ditambahkan.</div>
<?php elseif ($status === 'updated'): ?>
    <div style="background-color: #ecfdf5; color: #065f46; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #a7f3d0;">Produk berhasil diperbarui.</div>
<?php elseif ($status === 'deleted'): ?>
    <div style="background-color: #ecfdf5; color: #065f46; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #a7f3d0;">Produk berhasil dihapus.</div>
<?php endif; ?>

<?php if ($error === 'constraint'): ?>
    <div style="background-color: #fef2f2; color: #dc2626; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #fecaca; font-weight: bold;">
        Gagal menghapus! Produk ini sudah memiliki riwayat transaksi stok sehingga tidak boleh dihapus.
    </div>
<?php elseif ($error === 'unknown'): ?>
    <div style="background-color: #fef2f2; color: #dc2626; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #fecaca;">Terjadi kesalahan sistem saat menghapus data.</div>
<?php endif; ?>

<div class="card">
    <form method="GET" action="index.php" style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="search" placeholder="Cari nama atau SKU..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; flex-grow: 1;">
        
        <select name="category" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $filter_category == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="sort" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            <?php foreach ($sortOptions as $val => $label): ?>
                <option value="<?= $val ?>" <?= $sort == $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Filter</button>
        <?php if ($search || $filter_category || $sort !== 'name_asc'): ?>
            <a href="index.php" class="btn" style="background-color: #6b7280;">Reset</a>
        <?php endif; ?>
    </form>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Min. Stok</th>
                    <?php if (isAdmin()): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['sku']) ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><span class="badge-role" style="background-color: #10b981;"><?= htmlspecialchars($p['category_name']) ?></span></td>
                            <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td style="<?= $p['stock'] <= $p['minimum_stock'] ? 'color: #dc2626; font-weight: bold;' : '' ?>">
                                <?= $p['stock'] ?>
                            </td>
                            <td><?= $p['minimum_stock'] ?></td>
                            <?php if (isAdmin()): ?>
                                <td>
                                    <!-- Tombol Edit & Delete yang sudah aktif -->
                                    <a href="edit.php?id=<?= $p['id'] ?>" class="btn" style="padding: 4px 8px; font-size: 12px; background-color: #f59e0b;">Edit</a>
                                    
                                    <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px; border:none; cursor:pointer;">Hapus</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= isAdmin() ? '7' : '6' ?>" style="text-align: center; color: #6b7280;">Tidak ada produk yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Navigasi Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="margin-top: 20px; display: flex; gap: 5px; justify-content: center;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= buildUrl(['page' => $i]) ?>" 
                   style="padding: 5px 10px; text-decoration: none; border-radius: 4px; border: 1px solid #d1d5db; 
                          <?= $i == $page ? 'background-color: #2563eb; color: white;' : 'color: #374151;' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>