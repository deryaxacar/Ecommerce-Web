<?php
// includes/header_router.php

// Oturumu başlat (tek seferde)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kullanıcı rolüne göre uygun header dosyasını dahil eder.
 * - role 'admin' ise header_admin.php
 * - aksi halde header.php
 */
function include_header()
{
    // Oturum açmamışsa da genel header'e yönlendir
    if (empty($_SESSION['role'])) {
        include __DIR__ . '/header.php';
        return;
    }

    // Role bakarak header seçimi
    switch ($_SESSION['role']) {
        case 'admin':
            include __DIR__ . '/header_admin.php';
            break;
        default:
            include __DIR__ . '/header.php';
            break;
    }
}

// Otomatik çağırmak isterseniz bu satırı kaldırıp her sayfada include_header() deyin
// include_header();
?>