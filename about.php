<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

// Sayfa başlığı
$page_title = "Hakkımızda";

// Hakkımızda bilgilerini getir
$stmt = $db->query("SELECT * FROM about WHERE id = 1");
$about = $stmt->fetch(PDO::FETCH_ASSOC);

// Meta etiketleri
$meta_description = 'Seçkin Ajans hakkında bilgi edinin';
$meta_keywords = 'hakkımızda, matbaa, promosyon';

// Header'ı dahil et
require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Anasayfa</a></li>
            <li class="breadcrumb-item active">Hakkımızda</li>
        </ol>
    </div>
</nav>

<!-- Ana Bölüm -->
<section class="about-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <?php if (!empty($about['home_image'])): ?>
                    <img src="<?php echo SITE_URL . $about['home_image']; ?>" alt="<?php echo clean($about['page_title']); ?>" class="img-fluid rounded shadow">
                <?php else: ?>
                    <img src="<?php echo SITE_URL; ?>/assets/img/about.jpg" alt="Seçkin Ajans" class="img-fluid rounded shadow">
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <h1 class="mb-4"><?php echo clean($about['page_title'] ?? 'Hakkımızda'); ?></h1>
                <?php if (!empty($about['page_content'])): ?>
                    <?php echo $about['page_content']; ?>
                <?php else: ?>
                    <p>Hakkımızda içeriği burada görüntülenecektir.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Misyon ve Vizyon -->
<section class="mission-vision py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-dark mb-4">
                            <i class="fas fa-bullseye me-2"></i>Misyonumuz
                        </h3>
                        <?php if (!empty($about['mission'])): ?>
                            <?php echo $about['mission']; ?>
                        <?php else: ?>
                            <p>Misyonumuz, müşterilerimize en kaliteli matbaa ve promosyon ürünlerini sunarak, onların markalarını güçlendirmek ve iş hedeflerine ulaşmalarına yardımcı olmaktır.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-dark mb-4">
                            <i class="fas fa-eye me-2"></i>Vizyonumuz
                        </h3>
                        <?php if (!empty($about['vision'])): ?>
                            <?php echo $about['vision']; ?>
                        <?php else: ?>
                            <p>Vizyonumuz, sürekli gelişen teknolojileri takip ederek sektördeki liderliğimizi sürdürmek ve yenilikçi çözümler üreterek sektördeki öncü konumumuzu pekiştirmektir.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- İstatistikler -->
<section class="stats py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4 mb-md-0">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h3><span class="counter"><?php echo $about['happy_customers'] ?? 1000; ?></span>+</h3>
                <p class="mb-0">Memnun Müşteri</p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <i class="fas fa-project-diagram fa-3x mb-3"></i>
                <h3><span class="counter"><?php echo $about['completed_projects'] ?? 2500; ?></span>+</h3>
                <p class="mb-0">Tamamlanan Proje</p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <i class="fas fa-clock fa-3x mb-3"></i>
                <h3><span class="counter"><?php echo $about['years_experience'] ?? 20; ?></span>+</h3>
                <p class="mb-0">Yıllık Tecrübe</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-award fa-3x mb-3"></i>
                <h3><span class="counter"><?php echo $about['awards'] ?? 50; ?></span>+</h3>
                <p class="mb-0">Ödül & Başarı</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 