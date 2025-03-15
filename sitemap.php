<?php
require_once 'includes/config.php';

// XML başlığı
header('Content-Type: application/xml; charset=utf-8');

// Son güncelleme tarihi
$lastmod = date('Y-m-d');

// XML çıktısı
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Ana Sayfa -->
    <url>
        <loc><?php echo SITE_URL; ?></loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Sabit Sayfalar -->
    <?php
    $stmt = $db->query("SELECT slug, updated_at FROM pages WHERE status = 1");
    while ($page = $stmt->fetch(PDO::FETCH_ASSOC)):
    ?>
    <url>
        <loc><?php echo SITE_URL . '/' . $page['slug']; ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($page['updated_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endwhile; ?>
    
    <!-- Kategoriler -->
    <?php
    $stmt = $db->query("SELECT slug, updated_at FROM categories WHERE status = 1");
    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)):
    ?>
    <url>
        <loc><?php echo SITE_URL . '/kategori/' . $category['slug']; ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($category['updated_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endwhile; ?>
    
    <!-- Ürünler -->
    <?php
    $stmt = $db->query("SELECT slug, updated_at FROM products WHERE status = 1");
    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
    ?>
    <url>
        <loc><?php echo SITE_URL . '/urun/' . $product['slug']; ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($product['updated_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endwhile; ?>
</urlset> 