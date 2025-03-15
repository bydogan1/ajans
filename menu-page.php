<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

// Menü parametrelerini al
$main_menu = isset($_GET['main_menu']) ? clean($_GET['main_menu']) : '';
$sub_menu = isset($_GET['sub_menu']) ? clean($_GET['sub_menu']) : '';

// Ana menüyü getir
$stmt = $db->prepare("SELECT * FROM menus WHERE slug = ? AND status = 1 LIMIT 1");
$stmt->execute([$main_menu]);
$menu = $stmt->fetch();

if (!$menu) {
    header('Location: ' . SITE_URL);
    exit;
}

// Alt menü varsa getir
$sub_menu_info = null;
if ($sub_menu) {
    $stmt = $db->prepare("SELECT * FROM menus WHERE slug = ? AND parent_id = ? AND status = 1 LIMIT 1");
    $stmt->execute([$sub_menu, $menu['id']]);
    $sub_menu_info = $stmt->fetch();
}

// Ürünleri getir
$products_query = "SELECT * FROM urunler WHERE main_menu = ?";
$params = [$main_menu];

if ($sub_menu) {
    $products_query .= " AND sub_menu = ?";
    $params[] = $sub_menu;
}

$products_query .= " AND status = 1 ORDER BY created_at DESC";
$stmt = $db->prepare($products_query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Sayfa başlığı
$page_title = $sub_menu_info ? $sub_menu_info['name'] : $menu['name'];

// Debug bilgisi
error_log('Menu Page Debug:');
error_log('Main Menu: ' . $main_menu);
error_log('Sub Menu: ' . $sub_menu);

require_once 'includes/header.php';

// Bootstrap ve Font Awesome CDN
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!-- Ürünler Bölümü -->
<section class="products-section py-5">
    <div class="container">
        <h1 class="text-center mb-5"><?= $page_title ?></h1>
        
        <?php if (isset($menu['content']) && !empty($menu['content'])): ?>
            <div class="category-description mb-5">
                <?= $menu['content'] ?>
            </div>
        <?php endif; ?>

        <?php if ($products): ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 product-card">
                            <?php if ($product['image']): ?>
                                <?php
                                $image_path = "uploads/urunler/" . htmlspecialchars($product['image']);
                                $full_image_path = $_SERVER['DOCUMENT_ROOT'] . "/Seckin_ajans/" . $image_path;
                                $image_url = SITE_URL . '/' . $image_path;
                                ?>
                                <img src="<?php echo $image_url; ?>" 
                                     class="card-img-top product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="height: 200px; object-fit: cover; cursor: pointer;"
                                     data-bs-toggle="modal"
                                     data-bs-target="#imageModal"
                                     data-image-src="<?php echo $image_url; ?>"
                                     data-image-title="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="<?= SITE_URL ?>/assets/img/no-image.png" 
                                     class="card-img-top" 
                                     alt="Resim Yok"
                                     style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <?php if ($product['description']): ?>
                                    <p class="card-text"><?= mb_substr(strip_tags($product['description']), 0, 100) ?>...</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>Bu kategoride henüz ürün bulunmamaktadır.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Resim Modalı -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body text-center">
                <div class="loading-spinner d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                </div>
                <div class="image-container">
                    <img src="" id="modalImage" class="img-fluid" alt="">
                </div>
                <div class="zoom-controls">
                    <button class="btn btn-sm btn-outline-light" onclick="zoomImage(-0.1)">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <span class="zoom-level">100%</span>
                    <button class="btn btn-sm btn-outline-light" onclick="zoomImage(0.1)">
                        <i class="fas fa-search-plus"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal için JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageModal = document.getElementById('imageModal');
    const bsImageModal = new bootstrap.Modal(imageModal);
    const modalImg = document.getElementById('modalImage');
    const modalTitle = document.getElementById('imageModalLabel');
    const spinner = document.querySelector('.loading-spinner');
    let currentZoom = 1;
    let isDragging = false;
    let startX, startY, translateX = 0, translateY = 0;

    // Tüm ürün resimlerine tıklama olayı ekle
    document.querySelectorAll('.product-image').forEach(img => {
        img.addEventListener('click', function(e) {
            e.preventDefault();
            
            const title = this.getAttribute('data-image-title');
            const src = this.getAttribute('data-image-src');

            // Başlığı ayarla
            modalTitle.textContent = title;
            
            // Yükleme göstergesini göster
            spinner.classList.remove('d-none');
            modalImg.classList.add('d-none');
            
            // Resmi yükle
            modalImg.onload = function() {
                spinner.classList.add('d-none');
                modalImg.classList.remove('d-none');
                resetZoom();
            };
            
            modalImg.src = src;
            bsImageModal.show();
        });
    });

    // Zoom fonksiyonları
    window.zoomImage = function(factor) {
        const prevZoom = currentZoom;
        currentZoom = Math.max(0.5, Math.min(4, currentZoom + factor));
        
        if (prevZoom !== currentZoom) {
            modalImg.style.transform = `scale(${currentZoom}) translate(${translateX}px, ${translateY}px)`;
            updateZoomLevel();
        }
    };

    function updateZoomLevel() {
        const zoomLevel = document.querySelector('.zoom-level');
        if (zoomLevel) {
            zoomLevel.textContent = `${Math.round(currentZoom * 100)}%`;
        }
    }

    function resetZoom() {
        currentZoom = 1;
        translateX = 0;
        translateY = 0;
        modalImg.style.transform = 'scale(1) translate(0, 0)';
        updateZoomLevel();
    }

    // Sürükleme işlemleri
    modalImg.addEventListener('mousedown', startDragging);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDragging);
    modalImg.addEventListener('touchstart', startDragging);
    document.addEventListener('touchmove', drag);
    document.addEventListener('touchend', stopDragging);

    function startDragging(e) {
        if (currentZoom > 1) {
            isDragging = true;
            modalImg.classList.add('transition-disabled');
            
            if (e.type === 'mousedown') {
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
            } else if (e.type === 'touchstart') {
                startX = e.touches[0].clientX - translateX;
                startY = e.touches[0].clientY - translateY;
            }
        }
    }

    function drag(e) {
        if (!isDragging) return;
        e.preventDefault();

        let clientX, clientY;
        if (e.type === 'mousemove') {
            clientX = e.clientX;
            clientY = e.clientY;
        } else if (e.type === 'touchmove') {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }

        translateX = clientX - startX;
        translateY = clientY - startY;

        // Sınırları kontrol et
        const maxTranslate = 100 * (currentZoom - 1);
        translateX = Math.max(-maxTranslate, Math.min(maxTranslate, translateX));
        translateY = Math.max(-maxTranslate, Math.min(maxTranslate, translateY));

        modalImg.style.transform = `scale(${currentZoom}) translate(${translateX}px, ${translateY}px)`;
    }

    function stopDragging() {
        isDragging = false;
        modalImg.classList.remove('transition-disabled');
    }

    // Modal kapandığında temizle
    imageModal.addEventListener('hidden.bs.modal', function() {
        resetZoom();
        modalImg.src = '';
    });

    // Klavye kontrolleri
    document.addEventListener('keydown', function(e) {
        if (imageModal.classList.contains('show')) {
            switch(e.key) {
                case '+':
                case '=':
                    zoomImage(0.1);
                    break;
                case '-':
                    zoomImage(-0.1);
                    break;
                case 'Escape':
                    resetZoom();
                    break;
            }
        }
    });

    // Mouse wheel zoom
    imageModal.addEventListener('wheel', function(e) {
        if (e.ctrlKey) {
            e.preventDefault();
            const delta = e.deltaY * -0.01;
            zoomImage(delta);
        }
    }, { passive: false });
});
</script>

<style>
/* Modal stilleri */
.modal-dialog.modal-lg {
    max-width: 95%;
    margin: 1rem auto;
    height: 95vh;
}

.modal-content {
    background-color: rgba(0, 0, 0, 0.98);
    border: none;
    height: 100%;
    border-radius: 12px;
}

.modal-header {
    border-bottom: none;
    padding: 1.5rem;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 12px 12px 0 0;
}

.modal-header .modal-title {
    color: #fff;
    font-size: 1.25rem;
}

.modal-header .btn-close {
    background-color: #fff;
    opacity: 0.8;
    padding: 0.75rem;
}

.modal-body {
    padding: 0;
    position: relative;
    background-color: transparent;
    height: calc(100% - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-container {
    position: relative;
    overflow: hidden;
    margin: 0 auto;
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: rgba(255,255,255,0.05);
    border-radius: 8px;
    padding: 20px;
}

.modal-body img {
    max-height: 90vh;
    max-width: 95%;
    object-fit: contain;
    transition: transform 0.3s ease-out;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
}

.loading-spinner .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.25rem;
    color: rgba(255,255,255,0.8);
}

.zoom-controls {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1060;
    background-color: rgba(0, 0, 0, 0.8);
    padding: 15px;
    border-radius: 30px;
    display: flex;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    backdrop-filter: blur(5px);
}

.zoom-controls button {
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    background-color: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
}

.zoom-controls button:hover {
    background-color: rgba(255,255,255,0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.zoom-level {
    color: #fff;
    margin: 0 15px;
    font-size: 1rem;
    min-width: 70px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

/* Mobil cihazlar için düzenleme */
@media (max-width: 768px) {
    .modal-dialog.modal-lg {
        max-width: 100%;
        margin: 0;
        height: 100vh;
    }
    
    .modal-body img {
        max-height: 80vh;
    }
    
    .zoom-controls {
        bottom: 20px;
        padding: 8px;
    }
}

/* Zoom efekti için ek stiller */
.modal-body img.zoomed {
    cursor: move;
}

.modal-body img.transition-disabled {
    transition: none !important;
}

.product-image {
    transition: transform 0.3s ease-in-out;
    cursor: pointer;
    height: 200px;
    object-fit: contain !important;
    padding: 10px;
    background-color: #fff;
}

.product-image:hover {
    transform: scale(1.05);
}

.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    transform: translateY(-5px);
}

.card-body {
    padding: 1.25rem;
    background-color: #fff;
}
</style>

<?php require_once 'includes/footer.php'; ?> 