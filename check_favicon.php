<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

// Mevcut favicon ayarını kontrol et
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'site_favicon'");
$current_favicon = $stmt->fetchColumn();

echo "Mevcut favicon ayarı: " . ($current_favicon ?: 'Ayarlanmamış') . "\n";

// Favicon ayarını güncelle
$favicon_path = '/uploads/settings/favicon.png';
$stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_favicon'");
$result = $stmt->execute([$favicon_path]);

echo $result ? "Favicon ayarı başarıyla güncellendi: $favicon_path" : "Favicon güncellenirken hata oluştu";
?> 