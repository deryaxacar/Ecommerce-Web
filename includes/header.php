<?php
require '../config/db.php';
include '../includes/auth.php';

$unreadNotifs = [];
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT id, message,
               DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') AS when_
        FROM notifications
        WHERE user_id = ? AND is_read = 0
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadNotifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $unreadCount = count($unreadNotifs);
}
?>
<?php
require '../config/db.php';
include '../includes/auth.php';

$unreadNotifs = [];
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT id, message,
               DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') AS when_
        FROM notifications
        WHERE user_id = ? AND is_read = 0
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadNotifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $unreadCount = count($unreadNotifs);
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>E-Ticaret</title>
    <link rel="stylesheet" href="../assets/header.css">
</head>

<body style="margin:0;">
    <div class="sidebar">
        <h1>E-Ticaret Sitesi</h1>
        <nav style="display: flex; flex-direction: column; height: 88%;">
            <a href="index.php">Ana Sayfa</a>
            <a href="products.php">Ürünler</a>
            <a href="cart.php">Sepet</a>
            <a href="favorites.php">Favoriler</a>
            <a href="orders.php">Siparişler</a>
            <a href="register.php">Kayıt ol</a>
            <a href="logout.php" style="margin-top: auto;">Çıkış yap</a>
        </nav>
    </div>

    <div class="content">

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="notif">
                <!-- Bildirim ikonu -->
                <span id="bellIcon"></span>
                <?php if ($unreadCount > 0): ?>
                    <span class="badge"><?= $unreadCount ?></span>
                <?php endif; ?>

                <!-- Dropdown -->
                <ul class="notif-dropdown" id="notifDropdown">
                    <?php if ($unreadCount === 0): ?>
                        <li>Yeni bildirim yok.</li>
                    <?php else: ?>
                        <?php foreach ($unreadNotifs as $n): ?>
                            <li>
                                <?= htmlspecialchars($n['message']) ?>
                                <small><?= $n['when_'] ?></small>
                                <a href="mark_read.php?id=<?= $n['id'] ?>">Okundu</a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

        <?php else: ?>
            <a href="login.php">Giriş</a> |
            <a href="register.php">Kayıt</a>
        <?php endif; ?>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const bell = document.getElementById('bellIcon');
                const dropdown = document.getElementById('notifDropdown');

                // Zile tıklandığında aç/kapa
                bell.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });

                // Belgeye tıklanınca dropdown'u kapat
                document.addEventListener('click', function () {
                    dropdown.classList.remove('show');
                });
            });
        </script>
        <!-- … -->
</body>

</html>