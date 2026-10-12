<?php
// public/logout.php
session_start();

// Hapus semua data di dalam variabel global $_SESSION
$_SESSION = [];

// Hancurkan session dari server
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>