<?php
// public/admin_register.php

// 1) Oturumu başlat & yetki kontrolü
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// 2) DB bağlantısı
require_once __DIR__ . '/../config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // --- 3) Aynı username veya email var mı kontrol et ---
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    if ($check->fetchColumn() > 0) {
        $message = 'Bu kullanıcı adı veya e-posta zaten kayıtlı.';
    } else {
        // --- 4) Yeni admin kaydı ---
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')"
        );
        try {
            $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);
            $message = 'Yeni admin oluşturuldu. <a href="admin_login.php">Giriş yap</a>';
        } catch (PDOException $e) {
            // DB düzeyinde UNIQUE kısıtlaması varsa bu blok çalışabilir
            $message = 'Kayıt sırasında bir hata oluştu: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Admin Oluştur</title>
    <link rel="stylesheet" href="../assets/register.css">
</head>

<body>
    <div class="register-container">
        <h2>Yeni Admin Kayıt</h2>

        <?php if ($message): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>

        <form method="post">
            <input name="username" type="text" placeholder="Kullanıcı Adı" required>
            <input name="email" type="email" placeholder="E-posta" required>
            <input name="password" type="password" placeholder="Şifre" required>
            <button type="submit">Admin Oluştur</button>
        </form>
    </div>
</body>

</html>