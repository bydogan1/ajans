<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = "404 - Sayfa Bulunamadı";
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1">404</h1>
            <h2 class="mb-4">Sayfa Bulunamadı</h2>
            <p class="lead mb-5">Aradığınız sayfa bulunamadı veya taşınmış olabilir.</p>
            <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">Ana Sayfaya Dön</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>