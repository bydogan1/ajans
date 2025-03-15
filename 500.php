<?php
require_once 'includes/header.php';

// Meta etiketleri
echo generateMeta('Sunucu Hatası', 'Bir sunucu hatası oluştu', '500, hata, sunucu hatası');
?>

<div class="error-page py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <img src="/images/500.svg" alt="500" class="img-fluid mb-4" style="max-height: 300px;">
                <h1 class="display-1 text-danger mb-4">500</h1>
                <h2 class="mb-4">Sunucu Hatası</h2>
                <p class="lead mb-4">Üzgünüz, bir sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.</p>
                <div class="error-actions">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>Ana Sayfa
                    </a>
                    <a href="<?php echo SITE_URL; ?>/iletisim" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i>İletişim
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 