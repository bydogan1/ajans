<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'site_logo'");
$logo = $stmt->fetchColumn();

echo "Logo ayarı: " . ($logo ?: 'Ayarlanmamış');
?> 