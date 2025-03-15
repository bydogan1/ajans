<?php
require_once 'includes/config.php';

// URL'den gelen slug'ı kontrol et
$urun_slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

if (empty($urun_slug)) {
    // Slug yoksa ana sayfaya yönlendir
    header('Location: ' . SITE_URL);
    exit;
}

// Ürün bilgilerini getir
try {
    $stmt = $db->prepare("
        SELECT u.*, c.name as category_name, c.slug as category_slug 
        FROM urunler u 
        LEFT JOIN categories c ON u.category_id = c.id 
        WHERE u.slug = ? AND u.status = 1
    ");
    $stmt->execute([$urun_slug]);
    $urun = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$urun) {
        // Ürün bulunamadıysa ana sayfaya yönlendir
        header('Location: ' . SITE_URL);
        exit;
    }
} catch (PDOException $e) {
    // Hata durumunda ana sayfaya yönlendir
    error_log("Ürün sorgu hatası: " . $e->getMessage());
    header('Location: ' . SITE_URL);
    exit;
}

// Benzer ürünleri getir
try {
    $stmt = $db->prepare("
        SELECT u.*, c.name as category_name 
        FROM urunler u 
        LEFT JOIN categories c ON u.category_id = c.id 
        WHERE u.category_id = ? AND u.id != ? AND u.status = 1 
        ORDER BY u.created_at DESC 
        LIMIT 4
    ");
    $stmt->execute([$urun['category_id'], $urun['id']]);
    $benzer_urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Hata durumunda boş dizi ata
    $benzer_urunler = [];
    error_log("Benzer ürünler sorgu hatası: " . $e->getMessage());
}

// SEO meta bilgileri
$page_title = !empty($urun['meta_title']) ? $urun['meta_title'] : $urun['name'] . ' - ' . $settings['site_title'];
$meta_description = !empty($urun['meta_description']) ? $urun['meta_description'] : mb_substr(strip_tags($urun['description']), 0, 160);
$meta_keywords = !empty($urun['meta_keywords']) ? $urun['meta_keywords'] : '';

// Header'ı dahil et
require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Ana Sayfa</a></li>
            <?php if(!empty($urun['category_name'])): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/?id=<?php echo $urun['category_slug']; ?>">
                    <?php echo htmlspecialchars($urun['category_name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($urun['name']); ?>
            </li>
        </ol>
    </div>
</nav>

<!-- Ürün Detay -->
<div class="container my-5">
    <div class="row">
        <!-- Ürün Görseli -->
        <div class="col-md-6 mb-4">
            <div class="product-image-container">
                <?php if (!empty($urun['image'])): ?>
                <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($urun['image']); ?>"
                     class="img-fluid rounded shadow"
                     alt="<?php echo htmlspecialchars($urun['name']); ?>">
                <?php else: ?>
                <div class="no-image-container d-flex align-items-center justify-content-center bg-light rounded shadow">
                    <i class="fas fa-image fa-5x text-muted"></i>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Ürün Bilgileri -->
        <div class="col-md-6">
            <div class="product-details">
                <h1 class="mb-3"><?php echo htmlspecialchars($urun['name']); ?></h1>
                
                <?php if(!empty($urun['category_name'])): ?>
                <div class="category mb-3">
                    <span class="badge bg-primary">
                        <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($urun['category_name']); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($urun['description'])): ?>
                <div class="description mb-4">
                    <?php echo nl2br(htmlspecialchars($urun['description'])); ?>
                </div>
                <?php endif; ?>
                
                <div class="contact-info mt-4">
                    <h4 class="mb-3">Ürün Hakkında Bilgi Alın</h4>
                    <p>Bu ürün hakkında detaylı bilgi almak için bizimle iletişime geçebilirsiniz.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/?id=iletisim" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i> İletişime Geçin
                        </a>
                        <?php if(!empty($settings['site_whatsapp'])): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $settings['site_whatsapp']); ?>?text=<?php echo urlencode($urun['name'] . ' ürünü hakkında bilgi almak istiyorum.'); ?>" 
                           class="btn btn-success" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i> WhatsApp ile Sorun
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Benzer Ürünler -->
    <?php if(!empty($benzer_urunler)): ?>
    <div class="related-products mt-5">
        <h2 class="mb-4">Benzer Ürünler</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach($benzer_urunler as $benzer): ?>
            <div class="col">
                <div class="product-item">
                    <div class="product-image">
                        <?php if (!empty($benzer['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($benzer['image']); ?>"
                              class="product-img"
                              alt="<?php echo htmlspecialchars($benzer['name']); ?>">
                        <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $benzer['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($benzer['name']); ?></h3>
                        <?php if(!empty($benzer['category_name'])): ?>
                        <span class="category">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($benzer['category_name']); ?>
                        </span>
                        <?php endif; ?>
                        <button type="button" class="btn-details" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $benzer['id']; ?>">
                            Detaylar <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Benzer Ürünler Modalları -->
<?php foreach($benzer_urunler as $benzer): ?>
<div class="modal fade" id="productModal<?php echo $benzer['id']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $benzer['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel<?php echo $benzer['id']; ?>"><?php echo htmlspecialchars($benzer['name']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat" onclick="closeModal('productModal<?php echo $benzer['id']; ?>'); return false;"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="product-image-container">
                            <?php if (!empty($benzer['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($benzer['image']); ?>"
                                  class="img-fluid rounded"
                                  alt="<?php echo htmlspecialchars($benzer['name']); ?>">
                            <?php else: ?>
                            <div class="no-image-large d-flex align-items-center justify-content-center bg-light rounded" style="height: 300px;">
                                <i class="fas fa-image fa-5x text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($benzer['category_name'])): ?>
                        <div class="mb-3">
                            <span class="badge bg-primary">
                                <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($benzer['category_name']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($benzer['description'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Ürün Açıklaması</h6>
                            <div class="product-description">
                                <?php echo nl2br(htmlspecialchars($benzer['description'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('productModal<?php echo $benzer['id']; ?>'); return false;">Kapat</button>
                <a href="<?php echo SITE_URL; ?>/urun.php?slug=<?php echo !empty($benzer['slug']) ? $benzer['slug'] : 'urun'; ?>" class="btn btn-primary">
                    Ürün Sayfasına Git <i class="fas fa-external-link-alt ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- JavaScript -->
<script>
function closeModal(modalId) {
    var myModalEl = document.getElementById(modalId);
    var modal = bootstrap.Modal.getInstance(myModalEl);
    modal.hide();
}
</script>

<style>
/* Ürün detay stilleri */
.product-image-container {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.no-image-container {
    height: 400px;
    width: 100%;
}

.product-details {
    padding: 20px;
}

.product-details h1 {
    font-size: 2rem;
    font-weight: 600;
    color: #333;
}

.description {
    line-height: 1.7;
    color: #555;
}

.category .badge {
    font-size: 0.9rem;
    padding: 8px 12px;
}

/* Benzer ürünler stilleri */
.card-img-top-wrapper {
    position: relative;
    padding-top: 75%; /* 4:3 aspect ratio */
    overflow: hidden;
    background-color: #f8f9fa;
}

.card-img-top {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    line-height: 1.4;
    height: 3.1em;
    overflow: hidden;
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.card-text {
    font-size: 0.9rem;
    color: #6c757d;
    display: -webkit-box;
    line-clamp: 3;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0;
}

/* Responsive düzenlemeler */
@media (max-width: 768px) {
    .product-details h1 {
        font-size: 1.5rem;
    }
    
    .no-image-container {
        height: 250px;
    }
}

.related-product-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    transition: all 0.3s ease;
    display: -webkit-box;
    display: box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.related-product-description {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
    display: -webkit-box;
    display: box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<?php require_once 'includes/footer.php'; ?> 