<?php
// public/register.php

// 1) Oturumu başlat
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


require_once __DIR__ . '/../config/db.php';

$type = 'user';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1) Aynı kullanıcı adı veya e-posta var mı kontrol et
    $checkStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?"
    );
    $checkStmt->execute([$username, $email]);
    if ($checkStmt->fetchColumn() > 0) {
        $message = 'Bu kullanıcı adı veya e-posta adresi kullanılıyor.';
    } else {
        // 2) Yeni kayıt
        if ($type === 'user') {
            $insertSql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $redirect = 'login.php';
            $success = 'Kullanıcı kaydı başarılı. <a href="login.php">Giriş yap</a>';
        } else {
            $insertSql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
            $redirect = 'admin_login.php';
            $success = 'Admin kaydı başarılı. <a href="admin_login.php">Admin Girişi</a>';
        }

        $stmt = $pdo->prepare($insertSql);
        if (
            $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ])
        ) {
            $message = $success;
        } else {
            $message = 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Kayıt</title>
    <link rel="stylesheet" href="../assets/register.css">
</head>

<body>
    <div class="register-container">
        <h2>Kullanıcı Kayıt</h2>

        <?php if ($message): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>

        <form method="post">
            <input name="username" type="text" placeholder="Kullanıcı Adı" required>
            <input name="email" type="email" placeholder="E-posta" required>
            <input name="password" type="password" placeholder="Şifre" required>
            <button type="submit">Kayıt Ol</button>
            <?php if ($type === 'user'): ?>
                <p>Zaten kayıtlı mısınız? <a href="login.php">Giriş Yap</a></p>
            <?php else: ?>
                <p>Zaten admin misiniz? <a href="admin_login.php">Admin Girişi</a></p>
            <?php endif; ?>
        </form>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>