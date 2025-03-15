<?php
// Oturum açmış admin kullanıcısı varsa ana sayfaya yönlendir
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'admin') {
    header("Location: /");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakım Modu - <?php echo $settings['site_title']; ?></title>
    
    <!-- Favicon -->
    <?php if (!empty($settings['site_favicon'])): ?>
    <link rel="icon" href="<?php echo $settings['site_favicon']; ?>" type="image/x-icon">
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/Assets/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .maintenance-wrapper {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        
        .maintenance-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
            animation: wrench 2.5s ease infinite;
        }
        
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .maintenance-text {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            margin: 0 0.5rem;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .social-links a:hover {
            background: #0056b3;
        }
        
        @keyframes wrench {
            0% {
                transform: rotate(-12deg);
            }
            8% {
                transform: rotate(12deg);
            }
            10% {
                transform: rotate(24deg);
            }
            18% {
                transform: rotate(-24deg);
            }
            20% {
                transform: rotate(-24deg);
            }
            28% {
                transform: rotate(24deg);
            }
            30% {
                transform: rotate(24deg);
            }
            38% {
                transform: rotate(-24deg);
            }
            40% {
                transform: rotate(-24deg);
            }
            48% {
                transform: rotate(24deg);
            }
            50% {
                transform: rotate(24deg);
            }
            58% {
                transform: rotate(-24deg);
            }
            60% {
                transform: rotate(-24deg);
            }
            68% {
                transform: rotate(24deg);
            }
            75% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(0deg);
            }
        }
        
        /* Dark Mode */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #222;
            }
            
            .maintenance-title {
                color: #fff;
            }
            
            .maintenance-text {
                color: #ccc;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-wrapper">
        <!-- Logo -->
        <?php if (!empty($settings['site_logo'])): ?>
        <img src="<?php echo $settings['site_logo']; ?>" alt="<?php echo clean($settings['site_title']); ?>" 
             class="img-fluid mb-4" style="max-height: 80px;">
        <?php endif; ?>
        
        <i class="fas fa-wrench maintenance-icon"></i>
        
        <h1 class="maintenance-title">Bakım Modu</h1>
        
        <p class="maintenance-text">
            Sitemiz şu anda bakımda. Daha iyi hizmet verebilmek için çalışıyoruz. 
            Kısa süre içinde tekrar yayında olacağız.
        </p>
        
        <!-- Sosyal Medya -->
        <div class="social-links">
            <?php if (!empty($settings['facebook_url'])): ?>
            <a href="<?php echo clean($settings['facebook_url']); ?>" target="_blank">
                <i class="fab fa-facebook-f"></i>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($settings['twitter_url'])): ?>
            <a href="<?php echo clean($settings['twitter_url']); ?>" target="_blank">
                <i class="fab fa-twitter"></i>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($settings['instagram_url'])): ?>
            <a href="<?php echo clean($settings['instagram_url']); ?>" target="_blank">
                <i class="fab fa-instagram"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="/Assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 