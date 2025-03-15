<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $result = $stmt->execute(['/uploads/settings/favicon.png', 'site_favicon']);
    
    if ($result) {
        echo "Favicon ayarı başarıyla güncellendi.";
    } else {
        echo "Favicon ayarı güncellenirken bir hata oluştu.";
    }
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}
?> 