<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// .htaccess kuralını kontrol et
$htaccess_content = "RewriteEngine On\n";
$htaccess_content .= "RewriteBase /Seckin_ajans/\n\n";
$htaccess_content .= "# Menü sayfaları için kural\n";
$htaccess_content .= "RewriteRule ^([^/]+)/?$ menu-page.php?main_menu=$1 [L,QSA]\n";
$htaccess_content .= "RewriteRule ^([^/]+)/([^/]+)/?$ menu-page.php?main_menu=$1&sub_menu=$2 [L,QSA]\n";

// .htaccess dosyasını güncelle
file_put_contents('.htaccess', $htaccess_content, FILE_APPEND);

// menu-page.php dosyasını güncelle
$menu_page_content = '<?php
require_once "includes/config.php";
require_once "includes/functions.php";

// Menü parametrelerini al
$main_menu = isset($_GET["main_menu"]) ? clean($_GET["main_menu"]) : null;
$sub_menu = isset($_GET["sub_menu"]) ? clean($_GET["sub_menu"]) : null;

// Menü bilgilerini getir
$menu_info = null;
if ($main_menu) {
    $stmt = $db->prepare("SELECT * FROM menus WHERE slug = ? AND status = 1 LIMIT 1");
    $stmt->execute([$main_menu]);
    $menu_info = $stmt->fetch();
    
    if ($sub_menu) {
        $stmt = $db->prepare("
            SELECT m2.* 
            FROM menus m1 
            JOIN menus m2 ON m2.parent_id = m1.id 
            WHERE m1.slug = ? AND m2.slug = ? AND m2.status = 1 
            LIMIT 1
        ");
        $stmt->execute([$main_menu, $sub_menu]);
        $submenu_info = $stmt->fetch();
        if ($submenu_info) {
            $menu_info = $submenu_info;
        }
    }
}

if (!$menu_info) {
    header("HTTP/1.0 404 Not Found");
    include "404.php";
    exit;
}

// Ürünleri getir
$sql = "SELECT * FROM urunler WHERE status = 1";
$params = [];

if ($main_menu) {
    $sql .= " AND main_menu = ?";
    $params[] = $main_menu;
    
    if ($sub_menu) {
        $sql .= " AND sub_menu = ?";
        $params[] = $sub_menu;
    }
}

$sql .= " ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Sayfa başlığı
$page_title = $menu_info["name"];
require_once "includes/header.php";
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Ana Sayfa</a></li>
            <?php if ($main_menu && !$sub_menu): ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($menu_info["name"]) ?></li>
            <?php elseif ($main_menu && $sub_menu): ?>
                <li class="breadcrumb-item">
                    <a href="<?= SITE_URL ?>/<?= $main_menu ?>"><?= htmlspecialchars($menu_info["name"]) ?></a>
                </li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($submenu_info["name"]) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <h1 class="mb-4"><?= htmlspecialchars($menu_info["name"]) ?></h1>
    
    <?php if ($menu_info["description"]): ?>
        <div class="mb-4">
            <?= nl2br(htmlspecialchars($menu_info["description"])) ?>
        </div>
    <?php endif; ?>

    <?php if ($products): ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($product["image"]): ?>
                            <img src="<?= SITE_URL ?>/uploads/urunler/<?= htmlspecialchars($product["image"]) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($product["name"]) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product["name"]) ?></h5>
                            <?php if ($product["description"]): ?>
                                <p class="card-text"><?= nl2br(htmlspecialchars($product["description"])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Bu kategoride henüz ürün bulunmuyor.
        </div>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>';

// menu-page.php dosyasını güncelle
file_put_contents('menu-page.php', $menu_page_content);

echo "URL yapısı ve menü sayfası güncellendi. Artık şu URL'leri kullanabilirsiniz:\n";
echo "- http://localhost/Seckin_ajans/matbaa-urunleri\n";
echo "- http://localhost/Seckin_ajans/matbaa-urunleri/kartvizitler\n";
echo "- http://localhost/Seckin_ajans/promosyon-urunleri\n";
echo "- http://localhost/Seckin_ajans/promosyon-urunleri/kupalar\n";
?> 