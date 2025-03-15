<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

// En son yüklenen logo dosyasını bul
$logo_files = glob('uploads/settings/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
if (!empty($logo_files)) {
    // Dosyaları son değiştirilme tarihine göre sırala
    usort($logo_files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    // En son yüklenen dosyayı al ve göreceli yol olarak ayarla
    $latest_logo = '/uploads/settings/' . basename($logo_files[0]);
    
    // Veritabanını güncelle
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_logo'");
    $stmt->execute([$latest_logo]);
    
    echo "Logo yolu güncellendi: " . $latest_logo;
} else {
    echo "Logo dosyası bulunamadı!";
}
?> 