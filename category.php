<?php
require_once 'includes/header.php';

// Slug kontrolü
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';
if (!$slug) {
    header("Location: /");
    exit;
}

// Kategori bilgilerini getir
$stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND status = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: /");
    exit;
}

// Alt kategorileri getir
$stmt = $db->prepare("SELECT * FROM categories WHERE parent_id = ? AND status = 1 ORDER BY order_number");
$stmt->execute([$category['id']]);
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Kategori ve alt kategorilerindeki tüm ürünleri getir
$category_ids = [$category['id']];
foreach ($subcategories as $sub) {
    $category_ids[] = $sub['id'];
}

// Toplam ürün sayısı
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM products 
    WHERE category_id IN (" . implode(',', array_fill(0, count($category_ids), '?')) . ") 
    AND status = 1
");
$stmt->execute($category_ids);
$total = $stmt->fetchColumn();

// Ürünleri getir
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id IN (" . implode(',', array_fill(0, count($category_ids), '?')) . ") 
    AND p.status = 1 
    ORDER BY p.created_at DESC 
    LIMIT $offset, $per_page
");
$stmt->execute($category_ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Meta etiketleri
echo generateMeta(
    $category['name'], 
    $category['description'] ?: $category['name'] . ' kategorisindeki tüm ürünlerimiz', 
    $category['name'] . ', ' . ($category['keywords'] ?: $settings['site_keywords'])
);
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Anasayfa</a></li>
            <?php if ($category['parent_id']): ?>
            <?php
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$category['parent_id']]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>/kategori/<?php echo $parent['slug']; ?>">
                    <?php echo clean($parent['name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo clean($category['name']); ?></li>
        </ol>
    </div>
</nav>

<!-- Kategori İçeriği -->
<section class="category-content py-5">
    <div class="container">
        <div class="row">
            <!-- Yan Menü -->
            <div class="col-lg-3">
                <!-- Kategori Bilgileri -->
                <div class="category-info mb-4">
                    <h1 class="h3 mb-3"><?php echo clean($category['name']); ?></h1>
                    <?php if ($category['description']): ?>
                    <p class="text-muted"><?php echo clean($category['description']); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Alt Kategoriler -->
                <?php if ($subcategories): ?>
                <div class="subcategories mb-4">
                    <h5>Alt Kategoriler</h5>
                    <div class="list-group">
                        <?php foreach ($subcategories as $sub): ?>
                        <a href="<?php echo SITE_URL; ?>/kategori/<?php echo $sub['slug']; ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <?php echo clean($sub['name']); ?>
                            <?php
                            $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 1");
                            $stmt->execute([$sub['id']]);
                            $count = $stmt->fetchColumn();
                            ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $count; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Ürünler -->
            <div class="col-lg-9">
                <?php if ($products): ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if ($product['image']): ?>
                            <img src="<?php echo $product['image']; ?>" class="card-img-top" 
                                 alt="<?php echo clean($product['name']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo clean($product['name']); ?></h5>
                                <?php if ($product['category_name']): ?>
                                <p class="text-muted"><?php echo clean($product['category_name']); ?></p>
                                <?php endif; ?>
                                <p class="card-text">
                                    <?php echo mb_substr(strip_tags($product['description']), 0, 100) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price"><?php echo formatMoney($product['price']); ?></span>
                                    <a href="<?php echo SITE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                                       class="btn btn-primary">İncele</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Sayfalama -->
                <?php echo pagination($total, $per_page, $page, SITE_URL . '/kategori/' . $slug); ?>
                <?php else: ?>
                <div class="alert alert-info">
                    Bu kategoride henüz ürün bulunmuyor.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 