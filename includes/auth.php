<?php
// Oturum aktif değilse başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Şu anki script adı
$currentPage = basename($_SERVER['PHP_SELF']);

// Kamuya açık sayfalar
$publicPages = [
    'login.php',
    'register.php',
    'admin_login.php',
    'admin_register.php',
    'orders.php'
];

// Eğer publicPages’de değilse ve giriş yoksa — login’e gönder
if (!in_array($currentPage, $publicPages, true) && empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Yalnızca oturum açmış kullanıcılar için
if (!function_exists('require_login')) {
    function require_login()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}

// Yalnızca admin rolündeki kullanıcılar için
if (!function_exists('require_admin')) {
    function require_admin()
    {
        require_login();
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo "Bu sayfayı görüntüleme yetkiniz yok.";
            exit;
        }
    }
}
