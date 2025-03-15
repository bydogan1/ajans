<?php
http_response_code(403);
require_once __DIR__ . '/includes/header.php';

// Meta etiketleri
echo generateMeta('Erişim Engellendi', 'Bu sayfaya erişim izniniz yok', '403, hata, erişim engellendi');
?>

<div class="error-page py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="display-1 text-danger mb-4">403</h1>
                <h2 class="mb-4">Erişim Engellendi</h2>
                <p class="lead mb-4">Bu sayfaya erişim izniniz bulunmuyor.</p>
                <div class="error-actions">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg">
                        Ana Sayfaya Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Ürün Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid" alt="Ürün Görseli">
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 