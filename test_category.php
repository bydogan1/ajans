<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

// Kategoriyi al
$stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND status = 1");
$stmt->execute(['kartvizitler']);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if ($category) {
    $page_title = $category['name'];
    
    // Header'ı dahil et
    require_once 'includes/header.php';
    ?>
    <div class="container py-5">
        <h1><?php echo clean($category['name']); ?></h1>
        
        <?php if (!empty($category['description'])): ?>
        <div class="category-description mb-4">
            <?php echo nl2br(clean($category['description'])); ?>
        </div>
        <?php endif; ?>

        <!-- Kategorideki Ürünleri Listele -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php
            $stmt = $db->prepare("SELECT * FROM urunler WHERE category_id = ? AND status = 1");
            $stmt->execute([$category['id']]);
            $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($urunler)) {
                echo '<div class="col-12"><div class="alert alert-info">Bu kategoride henüz ürün bulunmuyor.</div></div>';
            } else {
                foreach ($urunler as $urun):
                ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($urun['image']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($urun['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($urun['name']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($urun['name']); ?></h5>
                            <?php if ($urun['description']): ?>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($urun['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php 
                endforeach;
            }
            ?>
        </div>
    </div>
    <?php
    // Footer'ı dahil et
    require_once 'includes/footer.php';
} else {
    echo "Kategori bulunamadı!";
}
?> 