<?php
require_once 'includes/config.php';
require_once 'admin/includes/functions.php';

try {
    // Menus tablosunu oluştur
    $db->exec("CREATE TABLE IF NOT EXISTS menus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_id INT DEFAULT NULL,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        link VARCHAR(255),
        content TEXT,
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        order_number INT DEFAULT 0,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Hakkımızda menüsünü ekle
    $stmt = $db->prepare("INSERT INTO menus (name, slug, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
    $stmt->execute(['Hakkımızda', 'hakkimizda', 1, 1]);

    echo "Menü tablosu oluşturuldu ve örnek veri eklendi.";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 