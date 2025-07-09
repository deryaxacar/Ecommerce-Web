<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();               // sadece login kontrolü

?>

<?php include '../includes/header_admin.php'; ?>

<main style="margin:20px;">
    <h1>Hoş geldin!</h1>
    <p>Siparişleri yönetmek için menüyü kullanabilirsin</p>
</main>

<?php include '../includes/footer.php'; ?>