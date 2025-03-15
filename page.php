<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

// Veritabanı bağlantısını kontrol et
try {
    $db->query("SELECT 1 FROM pages LIMIT 1");
} catch (PDOException $e) {
    // Pages tablosu yoksa oluştur
    $sql = file_get_contents('admin/sql/pages.sql');
    $db->exec($sql);
}

// Slug kontrolü
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';
if (!$slug) {
    header("Location: " . rtrim(SITE_URL, '/'));
    exit;
}

// Sayfa bilgilerini getir
$stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND status = 1");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    header("Location: " . rtrim(SITE_URL, '/') . "/404.php");
    exit;
}

// Header'ı dahil et
require_once 'includes/header.php';

// Meta etiketleri
echo generateMeta(
    $page['title'], 
    $page['description'] ?: mb_substr(strip_tags($page['content']), 0, 160), 
    $page['keywords'] ?: $settings['site_keywords']
);

// Tüm iç sayfalar için breadcrumb göster
?>
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Anasayfa</a></li>
            <li class="breadcrumb-item active"><?php echo clean($page['title']); ?></li>
        </ol>
    </div>
</nav>

<!-- Sayfa İçeriği -->
<section class="page-content py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Sayfa Başlığı -->
                <header class="page-header text-center mb-5">
                    <h1 class="display-4"><?php echo clean($page['title']); ?></h1>
                    <?php if ($page['description']): ?>
                    <p class="lead text-muted"><?php echo clean($page['description']); ?></p>
                    <?php endif; ?>
                </header>
                
                <!-- Sayfa Görseli -->
                <?php if ($page['image']): ?>
                <div class="page-image mb-5">
                    <img src="<?php echo $page['image']; ?>" alt="<?php echo clean($page['title']); ?>" 
                         class="img-fluid rounded shadow">
                </div>
                <?php endif; ?>
                
                <!-- Sayfa İçeriği -->
                <div class="page-text">
                    <?php echo $page['content']; ?>
                </div>
                
                <!-- Paylaşım Butonları -->
                <div class="share-buttons mt-5 text-center">
                    <p class="text-muted mb-3">Bu sayfayı paylaşın:</p>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/' . $page['slug']); ?>" 
                       target="_blank" class="btn btn-outline-primary me-2">
                        <i class="fab fa-facebook-f me-2"></i>Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/' . $page['slug']); ?>&text=<?php echo urlencode($page['title']); ?>" 
                       target="_blank" class="btn btn-outline-info me-2">
                        <i class="fab fa-twitter me-2"></i>Twitter
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($page['title'] . ' ' . SITE_URL . '/' . $page['slug']); ?>" 
                       target="_blank" class="btn btn-outline-success me-2">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <a href="mailto:?subject=<?php echo urlencode($page['title']); ?>&body=<?php echo urlencode(SITE_URL . '/' . $page['slug']); ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-envelope me-2"></i>E-posta
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 