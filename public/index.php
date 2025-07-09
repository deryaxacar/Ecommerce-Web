<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();               // sadece login kontrolü

?>

<?php
require_once __DIR__ . '/../includes/header_router.php';
include_header();
?>

<h1>Hoş geldiniz!</h1>
<p>Sitemize göz atmak için menüyü kullanabilirsiniz.</p>

<?php include '../includes/footer.php'; ?>