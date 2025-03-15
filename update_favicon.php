<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

// Favicon dizinini kontrol et ve oluştur
$favicon_dir = 'uploads/settings';
if (!file_exists($favicon_dir)) {
    mkdir($favicon_dir, 0777, true);
}

// Favicon dosyasını kopyala
$source_favicon = 'assets/img/favicon.png';
$target_favicon = $favicon_dir . '/favicon.png';

if (file_exists($source_favicon)) {
    if (copy($source_favicon, $target_favicon)) {
        // Veritabanını güncelle
        $favicon_path = '/uploads/settings/favicon.png';
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_favicon'");
        if ($stmt->execute([$favicon_path])) {
            echo "Favicon başarıyla güncellendi: " . $favicon_path;
        } else {
            echo "Veritabanı güncellenirken hata oluştu.";
        }
    } else {
        echo "Favicon dosyası kopyalanırken hata oluştu.";
    }
} else {
    echo "Kaynak favicon dosyası bulunamadı.";
}
?> 