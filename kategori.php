<?php
require_once 'includes/config.php';

// URL'den gelen slug'ı kontrol et
$category_slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

// Eğer slug yoksa URL'den id parametresini kontrol et (index.php'den yönlendirme için)
if (empty($category_slug) && isset($_GET['id'])) {
    $category_slug = clean($_GET['id']);
}

// Kategoriyi getir
$stmt = $db->prepare("
    SELECT c.*, parent.name as parent_name, parent.slug as parent_slug 
    FROM categories c
    LEFT JOIN categories parent ON c.parent_id = parent.id
    WHERE c.slug = ?
");
$stmt->execute([$category_slug]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    // Kategori bulunamadıysa ana sayfaya yönlendir
    header('Location: ' . SITE_URL);
    exit;
}

// Eğer bu bir üst kategori ise (parent_id NULL ise), tüm alt kategorilerdeki ürünleri getir
if ($category['parent_id'] === null) {
    $stmt = $db->prepare("
        SELECT u.*, c.name as category_name, c.slug as category_slug
        FROM urunler u
        LEFT JOIN categories c ON u.category_id = c.id
        WHERE (u.category_id = ? OR c.parent_id = ?) AND u.status = 1
        ORDER BY u.sort_order ASC, u.created_at DESC
    ");
    $stmt->execute([$category['id'], $category['id']]);
} else {
    // Alt kategori ise sadece o kategorideki ürünleri getir
    $stmt = $db->prepare("
        SELECT u.*, c.name as category_name, c.slug as category_slug
        FROM urunler u
        LEFT JOIN categories c ON u.category_id = c.id
        WHERE u.category_id = ? AND u.status = 1
        ORDER BY u.sort_order ASC, u.created_at DESC
    ");
    $stmt->execute([$category['id']]);
}

$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alt kategorileri getir
try {
    $stmt = $db->prepare("
        SELECT c.*, 
               (SELECT COUNT(*) FROM urunler u 
                LEFT JOIN categories subcat ON u.category_id = subcat.id
                WHERE (u.category_id = c.id OR subcat.parent_id = c.id) AND u.status = 1) as urun_sayisi
        FROM categories c
        WHERE c.parent_id = ? AND c.status = 1
        ORDER BY c.order_number ASC
    ");
    $stmt->execute([$category['id']]);
    $alt_kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Hata durumunda boş dizi ata
    $alt_kategoriler = [];
    error_log("Alt kategoriler sorgu hatası: " . $e->getMessage());
}

// SEO meta bilgileri
$page_title = !empty($category['meta_title']) ? $category['meta_title'] : $category['name'] . ' - ' . $settings['site_title'];
$meta_description = !empty($category['meta_description']) ? $category['meta_description'] : (!empty($category['description']) ? mb_substr(strip_tags($category['description']), 0, 160) : '');
$meta_keywords = !empty($category['meta_keywords']) ? $category['meta_keywords'] : '';

// Header'ı dahil et
require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Ana Sayfa</a></li>
            <?php if(!empty($category['parent_name'])): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/?id=<?php echo $category['parent_slug']; ?>">
                    <?php echo htmlspecialchars($category['parent_name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($category['name']); ?>
            </li>
        </ol>
    </div>
</nav>

<!-- Kategori Başlığı ve Açıklaması -->
<div class="container mt-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 mb-3"><?php echo htmlspecialchars($category['name']); ?></h1>
            <?php if(!empty($category['description'])): ?>
            <p class="lead text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Alt Kategoriler -->
    <?php if(!empty($alt_kategoriler)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="h4 mb-4">Alt Kategoriler</h2>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach($alt_kategoriler as $alt_kategori): ?>
                <div class="col">
                    <a href="<?php echo SITE_URL; ?>/?id=<?php echo $alt_kategori['slug']; ?>" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm">
                            <?php if(!empty($alt_kategori['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/categories/<?php echo htmlspecialchars($alt_kategori['image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($alt_kategori['name']); ?>">
                            <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                <i class="fas fa-folder-open fa-3x text-muted"></i>
                            </div>
                            <?php endif; ?>
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($alt_kategori['name']); ?></h5>
                                <?php if(isset($alt_kategori['urun_sayisi'])): ?>
                                <p class="text-muted mb-0"><?php echo $alt_kategori['urun_sayisi']; ?> ürün</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($category['parent_id'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?php echo SITE_URL; ?>/?id=<?php echo $category['parent_slug']; ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> <?php echo htmlspecialchars($category['parent_name']); ?> Kategorisine Dön
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Ürün Kartları -->
    <div class="row">
        <div class="col-12">
            <h2 class="h4 mb-4">Ürünler</h2>
            <?php if(!empty($urunler)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($urunler as $urun): ?>
                <div class="col">
                    <div class="product-item">
                        <div class="product-image">
                            <?php if (!empty($urun['image'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($urun['image']); ?>"
                                     class="product-img"
                                     alt="<?php echo htmlspecialchars($urun['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-overlay">
                                <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $urun['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($urun['name']); ?></h3>
                            <?php if (!empty($urun['category_name']) && $urun['category_id'] != $category['id']): ?>
                            <span class="category">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($urun['category_name']); ?>
                            </span>
                            <?php endif; ?>
                            <button type="button" class="btn-details" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $urun['id']; ?>">
                                Detaylar <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                Bu kategoride henüz ürün bulunmamaktadır.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Ürün Modalları -->
<?php foreach ($urunler as $urun): ?>
<div class="modal fade" id="productModal<?php echo $urun['id']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $urun['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel<?php echo $urun['id']; ?>"><?php echo htmlspecialchars($urun['name']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat" onclick="closeModal('productModal<?php echo $urun['id']; ?>'); return false;"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="product-image-container">
                            <?php if (!empty($urun['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($urun['image']); ?>"
                                  class="img-fluid rounded"
                                  alt="<?php echo htmlspecialchars($urun['name']); ?>">
                            <?php else: ?>
                            <div class="no-image-large d-flex align-items-center justify-content-center bg-light rounded" style="height: 300px;">
                                <i class="fas fa-image fa-5x text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($urun['category_name'])): ?>
                        <div class="mb-3">
                            <span class="badge bg-primary">
                                <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($urun['category_name']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($urun['description'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Ürün Açıklaması</h6>
                            <div class="product-description">
                                <?php echo nl2br(htmlspecialchars($urun['description'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('productModal<?php echo $urun['id']; ?>'); return false;">Kapat</button>
                <a href="<?php echo SITE_URL; ?>/urun.php?slug=<?php echo !empty($urun['slug']) ? $urun['slug'] : 'urun'; ?>" class="btn btn-primary">
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
/* Kart stilleri */
.card {
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

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

.no-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
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

/* Breadcrumb stilleri */
.breadcrumb {
    background: transparent;
    margin-bottom: 0;
    padding: 0;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.breadcrumb-item.active {
    color: #212529;
}

/* Responsive düzenlemeler */
@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
    
    .lead {
        font-size: 1rem;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    .card-text {
        font-size: 0.85rem;
    }
}

/* Modal stilleri */
.modal-content {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    will-change: transform;
}

.modal-header {
    border-bottom: 1px solid #f1f1f1;
    padding: 15px 20px;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    border-top: 1px solid #f1f1f1;
    padding: 15px 20px;
}

.product-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
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

.product-description {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
    display: -webkit-box;
    display: box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    background-color: #f8f9fa;
    height: 100%;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.no-image-large {
    border-radius: 8px;
    overflow: hidden;
    height: 100%;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Modal performans optimizasyonu */
.modal.fade {
    will-change: opacity;
    transition: opacity 0.15s linear;
}

.modal-backdrop.fade {
    will-change: opacity;
    transition: opacity 0.15s linear;
}

.modal-dialog {
    transform: none !important;
    will-change: opacity;
    transition: opacity 0.15s linear;
}

/* Modal arka planı düzeltmesi */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: #000;
}

.modal-backdrop.fade {
    opacity: 0;
}

.modal-backdrop.show {
    opacity: 0.5;
}

/* Modal açıkken body scroll engelleme düzeltmesi */
body.modal-open {
    overflow: hidden;
    padding-right: 0 !important;
}

@media (max-width: 767px) {
    .modal-dialog {
        margin: 0.5rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 