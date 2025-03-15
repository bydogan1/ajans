<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

// Veritabanı bağlantısını kontrol et
try {
    $db->query("SELECT 1");
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Sayfa ID'sini al
$page_id = clean($_GET['id'] ?? '');

// Menü HTML'ini oluştur
$menu_html = '';
$stmt = $db->query("SELECT * FROM menus WHERE parent_id IS NULL AND status = 1 ORDER BY order_number ASC");
$main_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($main_menus as $main) {
    // Alt menüleri kontrol et
    $stmt = $db->prepare("SELECT * FROM menus WHERE parent_id = ? AND status = 1 ORDER BY order_number ASC");
    $stmt->execute([$main['id']]);
    $sub_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sub_menus)) {
        // Alt menüsü yoksa normal link
        $menu_html .= '<li class="nav-item">';
        if ($main['slug'] === 'anasayfa') {
            $menu_html .= '<a class="nav-link" href="' . SITE_URL . '">' . clean($main['name']) . '</a>';
        } else {
            $menu_html .= '<a class="nav-link" href="' . SITE_URL . '/?id=' . $main['slug'] . '">' . clean($main['name']) . '</a>';
        }
        $menu_html .= '</li>';
    } else {
        // Alt menüsü varsa dropdown
        $menu_html .= '<li class="nav-item dropdown">';
        $menu_html .= '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">' . clean($main['name']) . '</a>';
        $menu_html .= '<ul class="dropdown-menu">';
        foreach ($sub_menus as $sub) {
            $menu_html .= '<li><a class="dropdown-item" href="' . SITE_URL . '/?id=' . $sub['slug'] . '">' . clean($sub['name']) . '</a></li>';
        }
        $menu_html .= '</ul>';
        $menu_html .= '</li>';
    }
}

// Sayfa içeriğini yükle
if ($page_id) {
    // Kategori sayfası kontrolü
    try {
        $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND status = 1 LIMIT 1");
        $stmt->execute([$page_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category) {
            // Kategori sayfasına yönlendir
            include 'kategori.php';
            exit;
        }
    } catch (PDOException $e) {
        error_log("Kategori kontrolü sorgu hatası: " . $e->getMessage());
        // Hata durumunda kategori kontrolünü geç
    }
    
    // Blog sayfası kontrolü
    if ($page_id == 'blog') {
        require_once 'blog.php';
        exit;
    }
    
    // İletişim sayfası kontrolü
    if ($page_id == 'contact' || $page_id == 'iletisim') {
        $page_title = "İletişim";
        $meta_description = "Seçkin Ajans ile iletişime geçin";
        $meta_keywords = "iletişim, adres, telefon, email";
        
        // İletişim bilgilerini settings tablosundan getir
        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_email', 'site_phone', 'site_address', 'google_maps', 'site_landline', 'contact_landline')");
            $settings_data = [];
            while ($row = $stmt->fetch()) {
                $settings_data[$row['setting_key']] = $row['setting_value'];
            }
            
            // Eğer settings_data boşsa varsayılan değerler ata
            if (empty($settings_data)) {
                $settings_data = [
                    'site_email' => '',
                    'site_phone' => '',
                    'site_address' => '',
                    'google_maps' => '',
                    'site_landline' => '',
                    'contact_landline' => ''
                ];
            }
        } catch (PDOException $e) {
            error_log("İletişim bilgileri sorgu hatası: " . $e->getMessage());
            $settings_data = [
                'site_email' => '',
                'site_phone' => '',
                'site_address' => '',
                'google_maps' => '',
                'site_landline' => '',
                'contact_landline' => ''
            ];
        }
        
        require_once 'includes/header.php';
        ?>
        <!-- İletişim Bölümü -->
        <!--
        <section id="contact" class="contact-section py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="h3 mb-4">Bize Ulaşın</h2>
                        <p class="lead">Sorularınız için bize ulaşın</p>

                        <?php if (!empty($settings_data['site_address'])): ?>
                        <p><i class="fas fa-map-marker-alt me-2"></i><?php echo nl2br(clean($settings_data['site_address'])); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($settings_data['site_phone'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-mobile-alt fs-4 text-primary"></i>
                            <p class="ms-4 mb-0"><?php echo clean($settings_data['site_phone']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($settings_data['site_landline'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone fs-4 text-primary"></i>
                            <p class="ms-4 mb-0"><?php echo clean($settings_data['site_landline']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($settings_data['contact_landline'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone fs-4 text-primary"></i>
                            <p class="ms-4 mb-0"><?php echo clean($settings_data['contact_landline']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($settings_data['site_email'])): ?>
                        <p><i class="fas fa-envelope me-2"></i><?php echo clean($settings_data['site_email']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($settings_data['google_maps'])): ?>
                            <div class="ratio ratio-16x9">
                                <?php echo $settings_data['google_maps']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        -->

        <style>
        .row-equal-height {
            display: flex;
            flex-wrap: wrap;
        }
        
        .row-equal-height > [class*='col-'] {
            display: flex;
            flex-direction: column;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        
        .contact-info i {
            color: #0d6efd;
            width: 20px;
            text-align: center;
        }
        
        .map-container {
            padding: 1rem;
            overflow: hidden;
        }
        
        .map-container .ratio {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        @media (max-width: 767.98px) {
            .map-container {
                min-height: 300px;
            }
            
            .map-container .ratio {
                position: absolute;
                top: 1rem;
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
            }
        }
        </style>
        <?php
        require_once 'includes/footer.php';
        exit;
    }
    
    // Menüyü bul
    $stmt = $db->prepare("SELECT * FROM menus WHERE slug = ? AND status = 1 LIMIT 1");
    $stmt->execute([$page_id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($menu) {
        // Sayfa başlığı
        $page_title = $menu['name'];

        // Ürünleri getir
        $stmt = $db->prepare("
            SELECT u.*, c.name as category_name 
            FROM urunler u 
            LEFT JOIN categories c ON u.category_id = c.id 
            WHERE (u.main_menu = ? OR u.sub_menu = ?) 
            AND u.status = 1 
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$page_id, $page_id]);
        $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // İçeriği göster
        require_once 'includes/header.php';
        ?>
        
        <!-- Sayfa Başlığı -->
        <div class="container mt-5">
            <h1 class="text-center mb-5"><?= htmlspecialchars($menu['name']) ?></h1>
            
            <?php if (isset($menu['content']) && !empty($menu['content'])): ?>
                <div class="category-description mb-5">
                    <?= $menu['content'] ?>
                </div>
                <?php endif; ?>

            <!-- Ürünler -->
            <div class="row g-4">
                <?php if ($urunler): ?>
                    <?php foreach ($urunler as $urun): ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="product-item">
                                <div class="product-image">
                                    <?php if (!empty($urun['image'])): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($urun['image']); ?>" class="product-img" alt="<?php echo htmlspecialchars($urun['name']); ?>">
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
                                    <h3><?= htmlspecialchars($urun['name']) ?></h3>
                                    <?php if (!empty($urun['category_name'])): ?>
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
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            Bu kategoride henüz ürün bulunmamaktadır.
                        </div>
                    </div>
                <?php endif; ?>
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

            <?php
            require_once 'includes/footer.php';
        } else {
        // Menü bulunamadı
        header('Location: ' . SITE_URL);
            exit;
    }
} else {
    // Anasayfa içeriği
    $page_title = $settings['site_title'];
    require_once 'includes/header.php';

    // Meta etiketleri
    $meta_title = isset($settings['site_title']) ? $settings['site_title'] : 'Seçkin Ajans';
    $meta_description = isset($settings['site_description']) ? $settings['site_description'] : 'Matbaa ve Promosyon Ürünleri';
    $meta_keywords = isset($settings['site_keywords']) ? $settings['site_keywords'] : 'matbaa, promosyon, ajans';

    // Aktif sliderları getir (sliders tablosundan)
    $stmt = $db->query("SELECT * FROM sliders WHERE status = 1 ORDER BY order_number ASC");
    $sliders = $stmt->fetchAll();

    // Son eklenen ürünleri getir
    try {
    $stmt = $db->query("
        SELECT p.*, c.name as category_name 
            FROM urunler p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 1 
        ORDER BY p.created_at DESC 
            LIMIT 12
    ");
    $latest_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Hata durumunda boş dizi ata
        $latest_products = [];
        error_log("Ürünler sorgu hatası: " . $e->getMessage());
    }

    // Ana kategorileri getir
    $stmt = $db->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM urunler u 
                LEFT JOIN categories subcat ON u.category_id = subcat.id
                WHERE (u.category_id = c.id OR subcat.parent_id = c.id) AND u.status = 1) as urun_sayisi
        FROM categories c 
        WHERE c.parent_id IS NULL AND c.status = 1 
        ORDER BY c.order_number ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hakkımızda bilgilerini getir
    $stmt = $db->query("SELECT * FROM about WHERE id = 1");
    $about = $stmt->fetch();

    // İletişim bilgilerini settings tablosundan getir
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_email', 'site_phone', 'site_address', 'google_maps', 'site_landline', 'contact_landline')");
        $contact_settings = [];
        while ($row = $stmt->fetch()) {
            $contact_settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        error_log("İletişim bilgileri sorgu hatası: " . $e->getMessage());
        $contact_settings = [];
    }

    // Son blog yazılarını getir
    $stmt = $db->query("SELECT * FROM blogs WHERE status = 1 ORDER BY created_at DESC LIMIT 3");
    $latest_blogs = $stmt->fetchAll();

    if (isset($_GET['id']) && $_GET['id'] === 'hakkimizda') {
        // include 'about.php';
    }
    ?>
    
    <!-- Slider -->
    <?php if (!empty($sliders)): ?>
    <section id="hero" class="hero section" style="margin-top: 0; padding-top: 0; margin-bottom: 0; padding-bottom: 0;">
        <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <?php foreach ($sliders as $index => $slider): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo SITE_URL . $slider['image']; ?>')">
                    <div class="carousel-container">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-8 offset-lg-2">
                        <?php if ($slider['title']): ?>
                                        <h2 class="animate__animated animate__fadeInDown"><?php echo clean($slider['title']); ?></h2>
                        <?php endif; ?>
                        <?php if ($slider['description']): ?>
                                        <p class="animate__animated animate__fadeInUp"><?php echo clean($slider['description']); ?></p>
                        <?php endif; ?>
                        <?php if ($slider['link']): ?>
                                        <a href="<?php echo $slider['link']; ?>" class="btn-get-started animate__animated animate__fadeInUp scrollto">Detaylar</a>
                        <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
            <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
            </a>
            <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
            </a>

            <ol class="carousel-indicators">
                <?php foreach ($sliders as $index => $slider): ?>
                <li data-bs-target="#hero-carousel" data-bs-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active"' : ''; ?>></li>
                <?php endforeach; ?>
            </ol>
    </div>
        
        <!-- Kıvrımlı alt kısım -->
        <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none">
            <defs>
                <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
            </defs>
            <g class="wave1">
                <use xlink:href="#wave-path" x="50" y="3" fill="rgba(255,255,255, .1)"></use>
            </g>
            <g class="wave2">
                <use xlink:href="#wave-path" x="50" y="0" fill="rgba(255,255,255, .2)"></use>
            </g>
            <g class="wave3">
                <use xlink:href="#wave-path" x="50" y="9" fill="#fff"></use>
            </g>
        </svg>
    </section>

    <style>
    /* Moderna Teması Slider Stilleri */
    #hero {
        width: 100%;
        height: 80vh;
        background-color: rgba(63, 73, 83, 0.8);
        overflow: hidden;
        position: relative;
    }

    #hero .carousel, #hero .carousel-inner, #hero .carousel-item, #hero .carousel-item::before {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
    }

    #hero .carousel-item {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    #hero .carousel-item::before {
        content: '';
        background-color: rgba(30, 35, 40, 0.6);
    }

    #hero .carousel-container {
        display: flex;
        justify-content: center;
        align-items: center;
        position: absolute;
        bottom: 0;
        top: 70px;
        left: 50px;
        right: 50px;
    }

    #hero .container {
        text-align: center;
    }

    #hero h2 {
        color: #fff;
        margin-bottom: 20px;
        font-size: 48px;
        font-weight: 700;
    }

    #hero p {
        -webkit-animation-delay: 0.4s;
        animation-delay: 0.4s;
        margin: 0 auto 30px auto;
        color: #fff;
    }

    #hero .carousel-inner .carousel-item {
        transition-property: opacity;
        background-position: center top;
    }

    #hero .carousel-inner .carousel-item,
    #hero .carousel-inner .active.carousel-item-start,
    #hero .carousel-inner .active.carousel-item-end {
        opacity: 0;
    }

    #hero .carousel-inner .active,
    #hero .carousel-inner .carousel-item-next.carousel-item-start,
    #hero .carousel-inner .carousel-item-prev.carousel-item-end {
        opacity: 1;
        transition: 0.5s;
    }

    #hero .carousel-inner .carousel-item-next,
    #hero .carousel-inner .carousel-item-prev,
    #hero .carousel-inner .active.carousel-item-start,
    #hero .carousel-inner .active.carousel-item-end {
        left: 0;
        transform: translate3d(0, 0, 0);
    }

    #hero .carousel-control-prev, #hero .carousel-control-next {
        width: 10%;
        top: 112px;
    }

    @media (max-width: 992px) {
        #hero .carousel-control-prev, #hero .carousel-control-next {
            top: 66px;
        }
    }

    #hero .carousel-control-next-icon, #hero .carousel-control-prev-icon {
        background: none;
        font-size: 36px;
        line-height: 1;
        width: auto;
        height: auto;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        padding: 10px;
        transition: 0.3s;
        color: rgba(255, 255, 255, 0.5);
    }

    #hero .carousel-control-next-icon:hover, #hero .carousel-control-prev-icon:hover {
        background: rgba(255, 255, 255, 0.3);
        color: rgba(255, 255, 255, 0.8);
    }

    #hero .btn-get-started {
        font-family: "Raleway", sans-serif;
        font-weight: 500;
        font-size: 14px;
        letter-spacing: 1px;
        display: inline-block;
        padding: 14px 32px;
        border-radius: 4px;
        transition: 0.5s;
        line-height: 1;
        color: #fff;
        -webkit-animation-delay: 0.8s;
        animation-delay: 0.8s;
        background: var(--primary-color);
    }

    #hero .btn-get-started:hover {
        background: #209dd8;
    }

    @media (max-width: 992px) {
        #hero {
            height: 100vh;
        }
        #hero .carousel-container {
            top: 8px;
        }
    }

    @media (max-width: 768px) {
        #hero h2 {
            font-size: 28px;
        }
    }

    @media (min-width: 1024px) {
        #hero .carousel-control-prev, #hero .carousel-control-next {
            width: 5%;
        }
    }

    @media (max-height: 500px) {
        #hero {
            height: 120vh;
        }
    }

    /* Dalga Animasyonu */
    .hero-waves {
        display: block;
        width: 100%;
        height: 60px;
        position: relative;
    }

    .wave1 use {
        -webkit-animation: move-forever1 10s linear infinite;
        animation: move-forever1 10s linear infinite;
        -webkit-animation-delay: -2s;
        animation-delay: -2s;
    }

    .wave2 use {
        -webkit-animation: move-forever2 8s linear infinite;
        animation: move-forever2 8s linear infinite;
        -webkit-animation-delay: -2s;
        animation-delay: -2s;
    }

    .wave3 use {
        -webkit-animation: move-forever3 6s linear infinite;
        animation: move-forever3 6s linear infinite;
        -webkit-animation-delay: -2s;
        animation-delay: -2s;
    }

    @-webkit-keyframes move-forever1 {
        0% {
            transform: translate(85px, 0%);
        }
        100% {
            transform: translate(-90px, 0%);
        }
    }

    @keyframes move-forever1 {
        0% {
            transform: translate(85px, 0%);
        }
        100% {
            transform: translate(-90px, 0%);
        }
    }

    @-webkit-keyframes move-forever2 {
        0% {
            transform: translate(-90px, 0%);
        }
        100% {
            transform: translate(85px, 0%);
        }
    }

    @keyframes move-forever2 {
        0% {
            transform: translate(-90px, 0%);
        }
        100% {
            transform: translate(85px, 0%);
        }
    }

    @-webkit-keyframes move-forever3 {
        0% {
            transform: translate(-90px, 0%);
        }
        100% {
            transform: translate(85px, 0%);
        }
    }

    @keyframes move-forever3 {
        0% {
            transform: translate(-90px, 0%);
        }
        100% {
            transform: translate(85px, 0%);
        }
    }
    </style>
    <?php endif; ?>

    <!-- Hizmetler (Features) -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-title text-center">
                <h2>Hizmetlerimiz</h2>
                <p>Profesyonel ekibimizle size özel çözümler sunuyoruz</p>
            </div>
            
                    <?php
                    // Aktif hizmetleri getir
            try {
                    $stmt = $db->prepare("
                        SELECT * FROM services 
                        WHERE status = 1 
                        ORDER BY order_number ASC
                    LIMIT 4
                    ");
                    $stmt->execute();
                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Hata durumunda boş dizi ata
                $services = [];
                error_log("Hizmetler sorgu hatası: " . $e->getMessage());
            }
            
            if (!empty($services)):
                $loop = 0; // AOS animasyonu için sayaç
                    foreach ($services as $service):
                    $isEven = $loop % 2 == 0; // Çift sayılı öğeler için
            ?>
            <div class="row feature-item <?php echo !$isEven ? 'flex-row-reverse' : ''; ?> mb-5">
                <div class="col-lg-6" data-aos="<?php echo $isEven ? 'fade-right' : 'fade-left'; ?>" data-aos-delay="100">
                    <div class="feature-img">
                        <?php if (!empty($service['image'])): ?>
                        <img src="<?php echo SITE_URL . '/uploads/services/' . $service['image']; ?>" class="img-fluid" alt="<?php echo !empty($service['name']) ? clean($service['name']) : 'Hizmet'; ?>">
                        <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/assets/img/features-<?php echo ($loop % 4) + 1; ?>.svg" class="img-fluid" alt="<?php echo !empty($service['name']) ? clean($service['name']) : 'Hizmet'; ?>">
                        <?php endif; ?>
                            </div>
                        </div>
                <div class="col-lg-6 pt-4 pt-lg-0" data-aos="<?php echo $isEven ? 'fade-left' : 'fade-right'; ?>" data-aos-delay="100">
                    <div class="feature-content">
                        <h3><?php echo !empty($service['name']) ? clean($service['name']) : 'Hizmet'; ?></h3>
                        <?php if (!empty($service['description'])): ?>
                        <p class="feature-description">
                            <?php echo mb_substr(strip_tags($service['description']), 0, 200); ?>...
                        </p>
                        <?php endif; ?>
                        
                        <ul class="feature-list">
                            <?php if (!empty($service['feature1'])): ?>
                            <li>
                                <i class="bi bi-check-circle"></i>
                                <?php echo clean($service['feature1']); ?>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($service['feature2'])): ?>
                            <li>
                                <i class="bi bi-check-circle"></i>
                                <?php echo clean($service['feature2']); ?>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($service['feature3'])): ?>
                            <li>
                                <i class="bi bi-check-circle"></i>
                                <?php echo clean($service['feature3']); ?>
                            </li>
                            <?php endif; ?>
                            <?php if (empty($service['feature1']) && empty($service['feature2']) && empty($service['feature3'])): ?>
                            <li>
                                <i class="bi bi-check-circle"></i>
                                Profesyonel ekip ve kaliteli hizmet
                            </li>
                            <li>
                                <i class="bi bi-check-circle"></i>
                                Müşteri memnuniyeti odaklı çalışma
                            </li>
                            <?php endif; ?>
                        </ul>
                        
                       
                    </div>
                </div>
            </div>
            <?php 
                $loop++;
                endforeach; 
            else: 
            ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">Henüz hizmet bulunmamaktadır.</div>
        </div>
    </div>
            <?php endif; ?>
        </div>
    </section>

    <style>
    /* Moderna Teması Features Bölümü Stilleri */
    .features {
        padding: 80px 0;
        overflow: hidden;
    }
    
    .section-title {
        text-align: center;
        padding-bottom: 30px;
    }
    
    .section-title h2 {
        font-size: 32px;
        font-weight: 700;
        position: relative;
        margin-bottom: 20px;
        padding-bottom: 20px;
        color: #212529;
    }
    
    .section-title h2::before {
        content: '';
        position: absolute;
        display: block;
        width: 120px;
        height: 1px;
        background: #ddd;
        bottom: 1px;
        left: calc(50% - 60px);
    }
    
    .section-title h2::after {
        content: '';
        position: absolute;
        display: block;
        width: 40px;
        height: 3px;
        background: var(--primary-color);
        bottom: 0;
        left: calc(50% - 20px);
    }
    
    .section-title p {
        margin-bottom: 0;
        color: #6c757d;
    }
    
    .feature-item {
        margin-bottom: 50px;
    }
    
    .feature-item:last-child {
        margin-bottom: 0;
    }
    
    .feature-img {
        position: relative;
    }
    
    .feature-img img {
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        max-height: 400px;
        object-fit: cover;
        width: 100%;
    }
    
    .feature-img::before {
        content: '';
        position: absolute;
        width: 70px;
        height: 70px;
        background: var(--primary-color);
        border-radius: 50%;
        right: -35px;
        top: 50%;
        transform: translateY(-50%);
        z-index: -1;
        opacity: 0.2;
    }
    
    .feature-img::after {
        content: '';
        position: absolute;
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        border-radius: 50%;
        left: -20px;
        bottom: 30px;
        z-index: -1;
        opacity: 0.2;
    }
    
    .flex-row-reverse .feature-img::before {
        left: -35px;
        right: auto;
    }
    
    .flex-row-reverse .feature-img::after {
        right: -20px;
        left: auto;
        top: 30px;
        bottom: auto;
    }
    
    .feature-content {
        padding: 30px;
    }
    
    .feature-content h3 {
        font-size: 28px;
        font-weight: 700;
        color: #212529;
        margin-bottom: 20px;
    }
    
    .feature-description {
        color: #6c757d;
        font-size: 16px;
        line-height: 1.7;
        margin-bottom: 25px;
    }
    
    .feature-list {
        list-style: none;
        padding: 0;
        margin-bottom: 25px;
    }
    
    .feature-list li {
        padding-left: 28px;
        position: relative;
        margin-bottom: 10px;
        color: #6c757d;
    }
    
    .feature-list li i {
        position: absolute;
        left: 0;
        top: 2px;
        font-size: 18px;
        color: var(--primary-color);
    }
    
    .btn-primary {
        background: var(--primary-color);
        border: 2px solid var(--primary-color);
        color: #fff;
        border-radius: 50px;
        padding: 8px 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: transparent;
        color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    /* Dark Mode için Feature Stilleri */
    [data-bs-theme="dark"] .section-title h2 {
        color: #f8f9fa;
    }
    
    [data-bs-theme="dark"] .section-title p {
        color: #adb5bd;
    }
    
    [data-bs-theme="dark"] .feature-content h3 {
        color: #f8f9fa;
    }
    
    [data-bs-theme="dark"] .feature-description,
    [data-bs-theme="dark"] .feature-list li {
        color: #adb5bd;
    }
    
    @media (max-width: 991px) {
        .feature-content {
            padding: 20px 0;
            margin-top: 30px;
        }
        
        .feature-img::before,
        .feature-img::after {
            display: none;
        }
        
        .feature-content h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .feature-item {
            margin-bottom: 50px;
        }
    }
    </style>

    <!-- Ana Sayfa İçeriği -->
    <div class="container py-5">
        <div class="row">
            <!-- Kategoriler -->
            <div class="col-12 mb-5">
                <h2 class="text-center mb-4">Kategoriler</h2>
            <div class="row g-4">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                        <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                            <div class="category-card">
                                <div class="category-card-inner">
                                    <div class="category-card-front">
                                        <div class="category-icon">
                                            <?php if (!empty($category['image'])): ?>
                                            <img src="<?php echo SITE_URL . '/uploads/categories/' . $category['image']; ?>"
                                                 alt="<?php echo clean($category['name']); ?>">
                                            <?php else: ?>
                                            <i class="fas fa-folder-open"></i>
                        <?php endif; ?>
                                        </div>
                                        <h3><?php echo clean($category['name']); ?></h3>
                                        <span class="product-count"><?php echo isset($category['urun_sayisi']) ? $category['urun_sayisi'] : 0; ?> ürün</span>
                                    </div>
                                    <div class="category-card-back">
                                        <h3><?php echo clean($category['name']); ?></h3>
                                        <p class="category-description">
                                            <?php 
                                            if (!empty($category['description'])) {
                                                echo mb_substr(strip_tags($category['description']), 0, 80) . '...';
                                            } else {
                                                echo 'Bu kategorideki ürünlerimizi keşfedin.';
                                            }
                                            ?>
                                        </p>
                                        <a href="<?php echo SITE_URL; ?>/?id=<?php echo !empty($category['slug']) ? $category['slug'] : 'kategoriler'; ?>" class="btn btn-primary btn-sm">Ürünleri Gör</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                            <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                Henüz kategori bulunmamaktadır.
                            </div>
                        </div>
                            <?php endif; ?>
                </div>
            </div>
            
            <!-- Son Eklenen Ürünler -->
            <div class="col-12 mb-5">
                <h2 class="text-center mb-4">Son Eklenen Ürünler</h2>
                <div class="row g-4">
                    <?php if (!empty($latest_products)): ?>
                        <?php foreach ($latest_products as $product): ?>
                        <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                            <div class="product-item">
                                <div class="product-image">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($product['image']); ?>" class="product-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div class="product-overlay">
                                        <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $product['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo clean($product['name']); ?></h3>
                                    <?php if (!empty($product['category_name'])): ?>
                                    <span class="category">
                                        <i class="fas fa-tag"></i> <?php echo clean($product['category_name']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <button type="button" class="btn-details" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $product['id']; ?>">
                                        Detaylar <i class="fas fa-arrow-right"></i>
                                    </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                        
                        <!-- Ürün Modalları -->
                        <?php foreach ($latest_products as $product): ?>
                        <div class="modal fade" id="productModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $product['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="productModalLabel<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat" onclick="closeModal('productModal<?php echo $product['id']; ?>'); return false;"></button>
            </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <div class="product-image-container">
                                                    <?php if (!empty($product['image'])): ?>
                                                    <img src="<?php echo SITE_URL; ?>/uploads/urunler/<?php echo htmlspecialchars($product['image']); ?>"
                                                          class="img-fluid rounded"
                                                          alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                    <?php else: ?>
                                                    <div class="no-image-large d-flex align-items-center justify-content-center bg-light rounded" style="height: 300px;">
                                                        <i class="fas fa-image fa-5x text-muted"></i>
        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if (!empty($product['category_name'])): ?>
                                                <div class="mb-3">
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($product['description'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="fw-bold mb-2">Ürün Açıklaması</h6>
                                                    <div class="product-description">
                                                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" onclick="closeModal('productModal<?php echo $product['id']; ?>'); return false;">Kapat</button>
                                        <a href="<?php echo SITE_URL; ?>/urun.php?slug=<?php echo !empty($product['slug']) ? $product['slug'] : 'urun'; ?>" class="btn btn-primary">
                                            Ürün Sayfasına Git <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">Henüz ürün bulunmamaktadır.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hakkımızda Bölümü -->
    <?php
    // About verilerini çek
    $stmt = $db->query("SELECT * FROM about WHERE id = 1");
    $about = $stmt->fetch();
    ?>
    
    <?php if (isset($about) && !empty($about)): ?>
    <!-- About sayfasını dahil et -->
    <?php 
        // include 'about.php';
    ?>
    <?php endif; ?>

    <!-- Son Blog Yazıları -->
    <section class="latest-blogs py-5">
        <div class="container">
            <h2 class="text-center mb-4">Son Blog Yazıları</h2>
            <div class="row">
                <?php if (!empty($latest_blogs)): ?>
                <?php foreach ($latest_blogs as $blog): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-img-top-wrapper">
                                <?php if (!empty($blog['image'])): ?>
                                    <?php
                                    // Eğer resim yolu http:// veya https:// ile başlıyorsa, tam URL olarak kullan
                                    if (strpos($blog['image'], 'http://') === 0 || strpos($blog['image'], 'https://') === 0) {
                                        $image_url = $blog['image'];
                                    } else {
                                        // Değilse, SITE_URL ile birleştir
                                        $image_url = SITE_URL . '/uploads/blogs/' . $blog['image'];
                                    }
                                    ?>
                                    <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo clean($blog['title']); ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/img/no-image.jpg" class="card-img-top" alt="Varsayılan Blog Görseli">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo clean($blog['title']); ?></h5>
                            <p class="card-text text-muted">
                                    <?php echo mb_substr(strip_tags($blog['content']), 0, 100); ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> <?php echo date('d.m.Y', strtotime($blog['created_at'])); ?>
                                </small>
                                    <a href="<?php echo SITE_URL; ?>/blog-detay.php?slug=<?php echo $blog['slug']; ?>"
                                       class="btn btn-primary btn-sm">
                                    Devamını Oku
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            Henüz blog yazısı bulunmamaktadır.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-outline-primary">Tüm Yazıları Gör</a>
            </div>
        </div>
    </section>

    <!-- Referanslar ve İş Ortaklarımız -->
    <section class="partners-section py-5">
        <div class="container">
            <div class="section-title text-center mb-5">
                <h2>Referanslar ve İş Ortaklarımız</h2>
                <p>Güvenilir iş ortaklarımız ve referanslarımız ile kaliteli hizmet sunuyoruz</p>
            </div>
            
            <?php
            // İş ortaklarını getir
            $stmt = $db->query("SELECT * FROM partners WHERE status = 1 ORDER BY order_number ASC");
            $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if (!empty($partners)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="partners-slider">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4 justify-content-center">
                            <?php foreach ($partners as $partner): ?>
                            <div class="col text-center">
                                <div class="partner-logo">
                                    <?php if (!empty($partner['link'])): ?>
                                    <a href="<?php echo clean($partner['link']); ?>" target="_blank" title="<?php echo clean($partner['name']); ?>">
                                    <?php endif; ?>
                                        
                                    <?php if (!empty($partner['logo'])): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/partners/<?php echo clean($partner['logo']); ?>" 
                                             alt="<?php echo clean($partner['name']); ?>" 
                                             class="img-fluid">
                                    <?php else: ?>
                                        <div class="no-logo">
                                            <?php echo clean($partner['name']); ?>
                                        </div>
                                    <?php endif; ?>
                                        
                                    <?php if (!empty($partner['link'])): ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Henüz referans veya iş ortağı bulunmamaktadır.
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php
    require_once 'includes/footer.php';
}
?>

<script src="<?php echo SITE_URL; ?>/assets/js/modal.js"></script>

<style>
/* Modal Stilleri */
.modal-backdrop {
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

<script>
function closeModal(modalId) {
    var myModalEl = document.getElementById(modalId);
    var modal = bootstrap.Modal.getInstance(myModalEl);
    modal.hide();
}
</script>

    <!-- Yukarı/Aşağı Hareket Butonları -->
    <div class="scroll-buttons">
        <button id="scrollToTopBtn" class="scroll-btn scroll-to-top" title="Yukarı Çık">
            <i class="fas fa-arrow-up"></i>
        </button>
        <button id="scrollToBottomBtn" class="scroll-btn scroll-to-bottom" title="Aşağı İn">
            <i class="fas fa-arrow-down"></i>
        </button>
    </div>

    <!-- Stil -->
    <style>
    .scroll-buttons {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .scroll-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.5);
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .scroll-btn:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }

    .scroll-btn.show {
        opacity: 1;
        visibility: visible;
    }

    .scroll-to-top {
        transform: translateY(20px);
    }

    .scroll-to-bottom {
        transform: translateY(-20px);
    }

    .scroll-btn.show {
        transform: translateY(0);
    }
    </style>

    <!-- Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const topBtn = document.getElementById('scrollToTopBtn');
        const bottomBtn = document.getElementById('scrollToBottomBtn');
        
        // Scroll butonlarının görünürlüğünü kontrol et
        function toggleScrollButtons() {
            const scrolled = window.scrollY;
            const windowHeight = window.innerHeight;
            const fullHeight = document.documentElement.scrollHeight;
            
            // Yukarı çıkma butonu
            if (scrolled > windowHeight / 2) {
                topBtn.classList.add('show');
            } else {
                topBtn.classList.remove('show');
            }
            
            // Aşağı inme butonu
            if (scrolled < (fullHeight - windowHeight * 1.5)) {
                bottomBtn.classList.add('show');
            } else {
                bottomBtn.classList.remove('show');
            }
        }

        // Yukarı çık butonu tıklama
        topBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Aşağı in butonu tıklama
        bottomBtn.addEventListener('click', function() {
            window.scrollTo({
                top: document.documentElement.scrollHeight,
                behavior: 'smooth'
            });
        });

        // Scroll olayını dinle
        window.addEventListener('scroll', toggleScrollButtons);
        
        // Sayfa yüklendiğinde kontrol et
        toggleScrollButtons();
    });
    </script>

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
    document.querySelectorAll('.product-img').forEach(img => {
            img.addEventListener('click', function(e) {
                e.preventDefault();
                
            const title = this.alt;
            const src = this.src;

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

<script src="<?php echo SITE_URL; ?>/assets/js/modal.js"></script>
</body>
</html>
