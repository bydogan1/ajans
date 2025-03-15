<?php
require_once 'includes/header.php';

// Slug kontrolü
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';
if (!$slug) {
    header("Location: /");
    exit;
}

// Ürün bilgilerini getir
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.status = 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: /");
    exit;
}

// Ürün resimlerini getir
$stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt->execute([$product['id']]);
$product_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Benzer ürünleri getir
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.status = 1 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product['id']]);
$similar_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Meta etiketleri
echo generateMeta(
    $product['name'], 
    mb_substr(strip_tags($product['description']), 0, 160), 
    $product['name'] . ', ' . $product['category_name']
);
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Anasayfa</a></li>
            <?php if ($product['category_name']): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/kategori/<?php echo $product['category_slug']; ?>">
                    <?php echo clean($product['category_name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo clean($product['name']); ?></li>
        </ol>
    </div>
</nav>

<!-- Ürün Detay -->
<section class="product-detail py-5">
    <div class="container">
        <div class="row">
            <!-- Ürün Resimleri -->
            <div class="col-md-6">
                <div class="product-gallery">
                    <!-- Ana Resim -->
                    <div class="product-main-image-container mb-3">
                        <img src="<?php echo SITE_URL; ?>/<?php echo $product['image']; ?>" alt="<?php echo clean($product['name']); ?>"
                             class="img-fluid rounded product-main-image">
                    </div>
                    
                    <!-- Küçük Resimler -->
                    <?php if ($product_images): ?>
                    <div class="product-thumbnails d-flex flex-wrap">
                        <div class="thumbnail-item me-2 mb-2">
                            <img src="<?php echo SITE_URL; ?>/<?php echo $product['image']; ?>"
                                 data-large-img="<?php echo SITE_URL; ?>/<?php echo $product['image']; ?>"
                                 alt="<?php echo clean($product['name']); ?>"
                                 class="img-fluid rounded product-thumbnail active">
                        </div>
                        <?php foreach ($product_images as $image): ?>
                        <div class="thumbnail-item me-2 mb-2">
                            <img src="<?php echo SITE_URL; ?>/<?php echo $image['image']; ?>"
                                 data-large-img="<?php echo SITE_URL; ?>/<?php echo $image['image']; ?>"
                                 alt="<?php echo clean($product['name']); ?>"
                                 class="img-fluid rounded product-thumbnail">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Ürün Bilgileri -->
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo clean($product['name']); ?></h1>
                
                <?php if ($product['category_name']): ?>
                <p class="text-muted">
                    Kategori: 
                    <a href="<?php echo SITE_URL; ?>/kategori/<?php echo $product['category_slug']; ?>">
                        <?php echo clean($product['category_name']); ?>
                    </a>
                </p>
                <?php endif; ?>
                
                <div class="price-box mb-4">
                    <span class="price h3"><?php echo formatMoney($product['price']); ?></span>
                    <?php if ($product['old_price'] > $product['price']): ?>
                    <span class="old-price text-muted text-decoration-line-through ms-2">
                        <?php echo formatMoney($product['old_price']); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="product-description mb-4">
                    <?php echo $product['description']; ?>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                <div class="stock-info mb-4">
                    <span class="badge bg-success">Stokta</span>
                    <span class="text-muted ms-2"><?php echo $product['stock']; ?> adet</span>
                </div>
                
                <div class="product-actions">
                    <a href="<?php echo SITE_URL; ?>/iletisim" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i>Teklif Al
                    </a>
                </div>
                <?php else: ?>
                <div class="stock-info mb-4">
                    <span class="badge bg-danger">Stokta Yok</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Benzer Ürünler -->
<section class="similar-products py-5 bg-light">
    <div class="container">
        <h2 class="mb-4">Benzer Ürünler</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($similar_products as $similar): ?>
            <div class="col">
                <div class="product-item">
                    <div class="product-image">
                        <?php if (!empty($similar['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>/<?php echo $similar['image']; ?>"
                             class="product-img"
                             alt="<?php echo clean($similar['name']); ?>">
                        <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $similar['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo clean($similar['name']); ?></h3>
                        <?php if (!empty($similar['category_name'])): ?>
                        <span class="category">
                            <i class="fas fa-tag"></i> <?php echo clean($similar['category_name']); ?>
                        </span>
                        <?php endif; ?>
                        <button type="button" class="btn-details" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $similar['id']; ?>">
                            Detaylar <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Benzer Ürünler Modalları -->
<?php foreach ($similar_products as $similar): ?>
<div class="modal fade" id="productModal<?php echo $similar['id']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $similar['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel<?php echo $similar['id']; ?>"><?php echo clean($similar['name']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat" onclick="closeModal('productModal<?php echo $similar['id']; ?>'); return false;"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="product-image-container">
                            <?php if (!empty($similar['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>/<?php echo $similar['image']; ?>"
                                  class="img-fluid rounded"
                                  alt="<?php echo clean($similar['name']); ?>">
                            <?php else: ?>
                            <div class="no-image-large d-flex align-items-center justify-content-center bg-light rounded" style="height: 300px;">
                                <i class="fas fa-image fa-5x text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($similar['category_name'])): ?>
                        <div class="mb-3">
                            <span class="badge bg-primary">
                                <i class="fas fa-tag me-1"></i> <?php echo clean($similar['category_name']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($similar['description'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Ürün Açıklaması</h6>
                            <div class="product-description">
                                <?php echo nl2br(clean($similar['description'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($similar['price'])): ?>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-2">Fiyat</h6>
                            <div class="product-price">
                                <?php echo formatMoney($similar['price']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('productModal<?php echo $similar['id']; ?>'); return false;">Kapat</button>
                <a href="<?php echo SITE_URL; ?>/urun.php?slug=<?php echo !empty($similar['slug']) ? $similar['slug'] : 'urun'; ?>" class="btn btn-primary">
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

<?php require_once 'includes/footer.php'; ?> 