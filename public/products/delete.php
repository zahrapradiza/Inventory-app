<?php
// public/products/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

requireRole('admin'); // Hanya admin

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header("Location: index.php?status=deleted");
            exit;
        } catch (PDOException $e) {
            // Kode 23000 berarti terjadi Constraint Violation (Pelanggaran Aturan Relasi)
            if ($e->getCode() == 23000) {
                header("Location: index.php?error=constraint");
                exit;
            }
            header("Location: index.php?error=unknown");
            exit;
        }
    }
}
header("Location: index.php");
exit; 