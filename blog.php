<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

// Sayfa başlığı ve meta bilgileri
$page_title = "Blog";
$meta_description = "Seçkin Ajans Blog Yazıları";
$meta_keywords = "blog, haberler, matbaa, promosyon";

// Header'ı dahil et
require_once 'includes/header.php';

// Blog yazılarını getir (sayfalama ile)
$page = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 9; // Her sayfada gösterilecek blog yazısı sayısı
$offset = ($page - 1) * $limit;

// Toplam blog yazısı sayısını al
$total_query = $db->query("SELECT COUNT(*) as total FROM blogs WHERE status = 1");
$total = $total_query->fetch()['total'];
$total_pages = ceil($total / $limit);

// Blog yazılarını getir
$stmt = $db->prepare("
    SELECT * FROM blogs 
    WHERE status = 1 
    ORDER BY created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll();
?>

<!-- Blog Listesi -->
<div class="container py-5 blog-container">
    <h1 class="text-center mb-5">Blog Yazıları</h1>
    
    <div class="row g-4">
        <?php foreach ($blogs as $blog): ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <?php if ($blog['image']): ?>
                <img src="<?php echo SITE_URL . '/uploads/blogs/' . $blog['image']; ?>" 
                     class="card-img-top" 
                     alt="<?php echo clean($blog['title']); ?>"
                     style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <img src="<?php echo SITE_URL; ?>/assets/img/no-image.jpg" 
                     class="card-img-top" 
                     alt="Varsayılan Blog Görseli"
                     style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body">
                    <h5 class="card-title"><?php echo clean($blog['title']); ?></h5>
                    <p class="card-text">
                        <?php echo mb_substr(strip_tags($blog['content']), 0, 150) . '...'; ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <?php echo date('d.m.Y', strtotime($blog['created_at'])); ?>
                        </small>
                        <a href="<?php echo SITE_URL; ?>/blog-detay/<?php echo $blog['slug']; ?>" 
                           class="btn btn-primary btn-sm">
                            Devamını Oku
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Sayfalama -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Blog sayfaları" class="mt-5">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?sayfa=<?php echo $page - 1; ?>">Önceki</a>
            </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?sayfa=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?sayfa=<?php echo $page + 1; ?>">Sonraki</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?> 