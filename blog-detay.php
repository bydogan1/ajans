<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

// Blog slug'ını al
$blog_slug = clean($_GET['slug'] ?? '');

if (empty($blog_slug)) {
    header("Location: " . SITE_URL . "/blog");
    exit;
}

// Blog yazısını getir
$stmt = $db->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 1");
$stmt->execute([$blog_slug]);
$blog = $stmt->fetch();

if (!$blog) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

// Sayfa başlığı ve meta bilgileri
$page_title = $blog['title'];
$meta_description = mb_substr(strip_tags($blog['content']), 0, 160);
$meta_keywords = $blog['keywords'] ?? "blog, haber, matbaa, promosyon";

// Header'ı dahil et
require_once 'includes/header.php';
?>

<!-- Blog İçeriği -->
<style>
    /* Blog detay sayfası için beyaz metin stilleri */
    .blog-detail-content {
        color: #ffffff !important;
    }
    
    .blog-detail-content h1, 
    .blog-detail-content h2, 
    .blog-detail-content h3, 
    .blog-detail-content h4, 
    .blog-detail-content h5, 
    .blog-detail-content h6,
    .blog-detail-content p,
    .blog-detail-content li,
    .blog-detail-content a,
    .blog-detail-content span,
    .blog-detail-content div,
    .blog-detail-content blockquote,
    .blog-detail-content figcaption,
    .blog-detail-content time,
    .blog-detail-content small,
    .blog-detail-content strong,
    .blog-detail-content em,
    .blog-detail-content code,
    .blog-detail-content pre {
        color: #ffffff !important;
    }
    
    .blog-detail-content a:hover {
        color: #f8f9fa !important;
        text-decoration: underline;
    }
    
    .blog-detail-meta {
        color: #e0e0e0 !important;
    }
    
    .blog-detail-meta span, 
    .blog-detail-meta i {
        color: #e0e0e0 !important;
    }
    
    .blog-detail-title {
        color: #ffffff !important;
    }
    
    .blog-detail-category {
        color: #e0e0e0 !important;
    }
    
    /* Yorum bölümü için beyaz metin stilleri */
    .comments-section h3,
    .comments-section p,
    .comments-section .comment-meta,
    .comments-section .comment-content {
        color: #ffffff !important;
    }
    
    /* Benzer Yazılar bölümü için beyaz metin stilleri */
    .related-posts h3,
    .related-posts h4,
    .related-posts p,
    .related-posts .card-title,
    .related-posts .card-text {
        color: #ffffff !important;
    }
    
    .related-posts .card {
        background-color: rgba(255, 255, 255, 0.1);
        border: none;
    }
    
    .related-posts .card:hover {
        background-color: rgba(255, 255, 255, 0.15);
    }
    
    /* Yorum formu için beyaz metin stilleri */
    .comment-form h3,
    .comment-form label,
    .comment-form .form-text {
        color: #ffffff !important;
    }
    
    /* Breadcrumb için beyaz metin stilleri */
    .breadcrumb-item,
    .breadcrumb-item a,
    .breadcrumb-item.active {
        color: #ffffff !important;
    }
    
    .breadcrumb-item a:hover {
        color: #f8f9fa !important;
        text-decoration: underline;
    }
    
    /* Kategori ve etiketler için beyaz metin stilleri */
    .blog-tags a,
    .blog-categories a {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .blog-tags a:hover,
    .blog-categories a:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }
</style>

<section class="blog-detail py-5">
    <div class="container blog-detail-content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <article class="blog-post">
                    <?php if ($blog['image']): ?>
                    <img src="<?php echo SITE_URL . '/uploads/blogs/' . $blog['image']; ?>"
                          class="img-fluid rounded mb-4"
                          alt="<?php echo clean($blog['title']); ?>">
                    <?php endif; ?>

                    <h1 class="blog-post-title mb-3">
                        <?php echo clean($blog['title']); ?>
                    </h1>

                    <div class="blog-post-meta text-muted mb-4">
                        <span class="me-3">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?php echo date('d.m.Y', strtotime($blog['created_at'])); ?>
                        </span>
                        <?php if (!empty($blog['author'])): ?>
                        <span>
                            <i class="fas fa-user me-2"></i>
                            <?php echo clean($blog['author']); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="blog-post-content">
                        <?php echo $blog['content']; ?>
                    </div>

                    <?php if (!empty($blog['tags'])): ?>
                    <div class="blog-post-tags mt-4">
                        <i class="fas fa-tags me-2"></i>
                        <?php
                        $tags = explode(',', $blog['tags']);
                        foreach ($tags as $tag):
                            $tag = trim($tag);
                            if (!empty($tag)):
                        ?>
                            <span class="badge bg-secondary me-2"><?php echo clean($tag); ?></span>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                    <?php endif; ?>
                </article>

                <!-- Diğer Blog Yazıları -->
                <div class="other-posts mt-5">
                    <h3 class="mb-4">Diğer Blog Yazıları</h3>
                    <div class="row g-4">
                        <?php
                        // Son 3 blog yazısını getir (mevcut yazı hariç)
                        $stmt = $db->prepare("
                            SELECT * FROM blogs 
                            WHERE status = 1 AND id != ? 
                            ORDER BY created_at DESC 
                            LIMIT 3
                        ");
                        $stmt->execute([$blog['id']]);
                        $other_blogs = $stmt->fetchAll();

                        foreach ($other_blogs as $other_blog):
                        ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <?php if ($other_blog['image']): ?>
                                <img src="<?php echo SITE_URL . '/uploads/blogs/' . $other_blog['image']; ?>"
                                      class="card-img-top"
                                      alt="<?php echo clean($other_blog['title']); ?>"
                                      style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo clean($other_blog['title']); ?></h5>
                                    <p class="card-text">
                                        <?php echo mb_substr(strip_tags($other_blog['content']), 0, 100) . '...'; ?>
                                    </p>
                                    <a href="<?php echo SITE_URL; ?>/blog-detay/<?php echo $other_blog['slug']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        Devamını Oku
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Blog Listesine Dön -->
                <div class="text-center mt-5">
                    <a href="<?php echo SITE_URL; ?>/blog" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Blog Listesine Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.blog-post {
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.blog-post-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
}

.blog-post-meta {
    font-size: 0.9rem;
}

.blog-post-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #444;
}

.blog-post-content img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
    border-radius: 5px;
}

.blog-post-tags .badge {
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
}

@media (max-width: 768px) {
    .blog-post {
        padding: 1rem;
    }
    
    .blog-post-title {
        font-size: 2rem;
    }
    
    .blog-post-content {
        font-size: 1rem;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?> 