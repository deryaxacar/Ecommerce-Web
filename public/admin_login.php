<?php
// public/admin_login.php

// 1) Oturumu başlat
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 2) Veritabanı bağlantısı
require_once __DIR__ . '/../config/db.php';


// 3) Zaten admin oturumu açıksa direkt paneline yönlendir
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin_index.php');
    exit;
}

// 4) POST ile form gönderildiyse giriş kontrolü
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$_POST['email'], 'admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: admin_index.php');
        exit;
    } else {
        $error = 'Geçersiz admin bilgileri.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Admin Giriş</title>
    <link rel="stylesheet" href="../assets/login.css">
</head>

<body>

    <!-- Kullanıcı/Admin geçiş -->
    <div class="login-switch">
        <a href="login.php">Kullanıcı Girişi</a>
        <a href="admin_login.php">Admin Girişi</a>
    </div>

    <!-- Admin giriş formu -->
    <div class="login-container">
        <h2>Admin Giriş</h2>
        <form method="post">
            <input name="email" type="email" placeholder="Admin E-posta" required>
            <input name="password" type="password" placeholder="Şifre" required>
            <button type="submit">Giriş Yap</button>
            <p>kayıtlı değil misiniz? <a href="register.php">Kayıt Ol</a> </p>
        </form>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

</body>

</html>