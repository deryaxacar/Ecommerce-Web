<?php
// public/login.php

// 1) Oturumu başlat
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 2) Eğer zaten giriş yapmışsa, direkt ürünler sayfasına yolla
if (!empty($_SESSION['user_id'])) {
    header('Location: products.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// 2) POST ile giriş işlemi
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($_POST['password'], $user['password'])) {
        // Başarılı giriş
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = 'Geçersiz e-posta veya şifre.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Giriş</title>
    <link rel="stylesheet" href="../assets/login.css">
</head>

<body>

    <!-- Giriş/Admin geçiş -->
    <div class="login-switch">
        <a href="login.php">Kullanıcı Girişi</a>
        <a href="admin_login.php">Admin Girişi</a>
    </div>

    <!-- Giriş formu -->
    <div class="login-container">
        <h2>Kullanıcı Giriş</h2>
        <form method="post">
            <input name="email" type="email" placeholder="E-posta" required>
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