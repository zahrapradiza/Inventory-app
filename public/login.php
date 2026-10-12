<?php
// public/login.php

// Aktifkan error reporting sementara untuk proses testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
session_start();

// Jika sudah login, langsung tendang ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Ambil data user dari database dengan prepared statement
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Verifikasi keberadaan user dan password hash-nya
    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerasi ID session untuk mencegah serangan Session Fixation
        session_regenerate_id(true);
        
        // Simpan data penting ke dalam session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #1f2937; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #4b5563; font-size: 14px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        .btn-login { width: 100%; padding: 10px; background-color: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn-login:hover { background-color: #1d4ed8; }
        .alert-error { color: #dc2626; font-size: 14px; text-align: center; margin-bottom: 15px; background: #fef2f2; border: 1px solid #fecaca; padding: 10px; border-radius: 4px;}
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Login Sistem</h2>
        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>
    </div>
</body>
</html>