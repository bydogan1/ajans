<?php
// Veritabanı bağlantısı
require_once 'includes/config.php';

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Stil ekle
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
    h3 { color: #2980b9; margin-top: 30px; }
    pre { background-color: #f8f9fa; padding: 15px; border-radius: 5px; overflow: auto; }
    .error { color: #e74c3c; font-weight: bold; }
    .success { color: #27ae60; font-weight: bold; }
    .warning { color: #f39c12; font-weight: bold; }
    .table-info { margin-bottom: 20px; }
    .fix-button { 
        display: inline-block; 
        background-color: #3498db; 
        color: white; 
        padding: 8px 15px; 
        text-decoration: none; 
        border-radius: 4px; 
        margin-top: 10px; 
    }
    .fix-button:hover { 
        background-color: #2980b9; 
    }
</style>';

echo '<h1>Veritabanı Düzeltme Aracı</h1>';

// Tabloları listele
echo '<h2>Mevcut Veritabanı Tabloları</h2>';
try {
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo '<div class="table-info">';
    echo '<p>Toplam <span class="success">' . count($tables) . '</span> tablo bulundu.</p>';
    echo '</div>';
    
    echo '<pre>';
    print_r($tables);
    echo '</pre>';
    
    // Eksik tabloları kontrol et
    $required_tables = ['categories', 'services', 'urunler'];
    $missing_tables = array_diff($required_tables, $tables);
    
    if (!empty($missing_tables)) {
        echo '<div class="error">';
        echo '<p>Eksik tablolar tespit edildi:</p>';
        echo '<ul>';
        foreach ($missing_tables as $table) {
            echo '<li>' . htmlspecialchars($table) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        
        // Eksik tabloları oluştur
        echo '<h3>Eksik Tabloları Oluşturma</h3>';
        
        foreach ($missing_tables as $table) {
            echo '<p>Tablo oluşturuluyor: ' . htmlspecialchars($table) . '</p>';
            
            try {
                switch ($table) {
                    case 'categories':
                        $db->exec("
                            CREATE TABLE IF NOT EXISTS categories (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                parent_id INT DEFAULT NULL,
                                name VARCHAR(255) NOT NULL,
                                slug VARCHAR(255) NOT NULL UNIQUE,
                                description TEXT,
                                image VARCHAR(255),
                                order_number INT DEFAULT 0,
                                status TINYINT(1) DEFAULT 1,
                                is_featured TINYINT(1) DEFAULT 0,
                                meta_title VARCHAR(255),
                                meta_description TEXT,
                                meta_keywords VARCHAR(255),
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                        ");
                        echo '<p class="success">Kategoriler tablosu başarıyla oluşturuldu.</p>';
                        break;
                        
                    case 'services':
                        $db->exec("
                            CREATE TABLE IF NOT EXISTS services (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                name VARCHAR(255) NOT NULL,
                                slug VARCHAR(255) NOT NULL UNIQUE,
                                description TEXT,
                                feature1 VARCHAR(255),
                                feature2 VARCHAR(255),
                                feature3 VARCHAR(255),
                                image VARCHAR(255),
                                icon VARCHAR(50),
                                order_number INT DEFAULT 0,
                                status TINYINT(1) DEFAULT 1,
                                is_featured TINYINT(1) DEFAULT 0,
                                meta_title VARCHAR(255),
                                meta_description TEXT,
                                meta_keywords VARCHAR(255),
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                        ");
                        echo '<p class="success">Hizmetler tablosu başarıyla oluşturuldu.</p>';
                        break;
                        
                    case 'urunler':
                        $db->exec("
                            CREATE TABLE IF NOT EXISTS urunler (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                category_id INT,
                                name VARCHAR(255) NOT NULL,
                                slug VARCHAR(255) NOT NULL UNIQUE,
                                description TEXT,
                                image VARCHAR(255),
                                status TINYINT(1) DEFAULT 1,
                                is_featured TINYINT(1) DEFAULT 0,
                                meta_title VARCHAR(255),
                                meta_description TEXT,
                                meta_keywords VARCHAR(255),
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                        ");
                        echo '<p class="success">Ürünler tablosu başarıyla oluşturuldu.</p>';
                        break;
                }
            } catch (PDOException $e) {
                echo '<p class="error">Tablo oluşturma hatası (' . htmlspecialchars($table) . '): ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }
    } else {
        echo '<p class="success">Tüm gerekli tablolar mevcut.</p>';
    }
    
    // Tablo sütunlarını kontrol et
    echo '<h3>Tablo Sütunlarını Kontrol Etme</h3>';
    
    // Kategoriler tablosu sütunlarını kontrol et
    if (in_array('categories', $tables)) {
        try {
            $stmt = $db->query("DESCRIBE categories");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $required_columns = ['id', 'name', 'slug', 'status'];
            $missing_columns = array_diff($required_columns, $columns);
            
            if (!empty($missing_columns)) {
                echo '<p class="warning">Kategoriler tablosunda eksik sütunlar:</p>';
                echo '<ul>';
                foreach ($missing_columns as $column) {
                    echo '<li>' . htmlspecialchars($column) . '</li>';
                    
                    // Eksik sütunları ekle
                    try {
                        switch ($column) {
                            case 'slug':
                                $db->exec("ALTER TABLE categories ADD COLUMN slug VARCHAR(255) NOT NULL UNIQUE AFTER name");
                                echo '<p class="success">Slug sütunu eklendi.</p>';
                                break;
                            case 'status':
                                $db->exec("ALTER TABLE categories ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER order_number");
                                echo '<p class="success">Status sütunu eklendi.</p>';
                                break;
                        }
                    } catch (PDOException $e) {
                        echo '<p class="error">Sütun ekleme hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
                echo '</ul>';
            } else {
                echo '<p class="success">Kategoriler tablosu sütunları tam.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">Kategoriler tablosu kontrol hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    // Hizmetler tablosu sütunlarını kontrol et
    if (in_array('services', $tables)) {
        try {
            $stmt = $db->query("DESCRIBE services");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $required_columns = ['id', 'name', 'slug', 'description', 'status'];
            $missing_columns = array_diff($required_columns, $columns);
            
            if (!empty($missing_columns)) {
                echo '<p class="warning">Hizmetler tablosunda eksik sütunlar:</p>';
                echo '<ul>';
                foreach ($missing_columns as $column) {
                    echo '<li>' . htmlspecialchars($column) . '</li>';
                    
                    // Eksik sütunları ekle
                    try {
                        switch ($column) {
                            case 'slug':
                                $db->exec("ALTER TABLE services ADD COLUMN slug VARCHAR(255) NOT NULL UNIQUE AFTER name");
                                echo '<p class="success">Slug sütunu eklendi.</p>';
                                break;
                            case 'description':
                                $db->exec("ALTER TABLE services ADD COLUMN description TEXT AFTER slug");
                                echo '<p class="success">Description sütunu eklendi.</p>';
                                break;
                            case 'status':
                                $db->exec("ALTER TABLE services ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER order_number");
                                echo '<p class="success">Status sütunu eklendi.</p>';
                                break;
                        }
                    } catch (PDOException $e) {
                        echo '<p class="error">Sütun ekleme hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
                echo '</ul>';
            } else {
                echo '<p class="success">Hizmetler tablosu sütunları tam.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">Hizmetler tablosu kontrol hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    // Ürünler tablosu sütunlarını kontrol et
    if (in_array('urunler', $tables)) {
        try {
            $stmt = $db->query("DESCRIBE urunler");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $required_columns = ['id', 'name', 'slug', 'description', 'image', 'category_id', 'status'];
            $missing_columns = array_diff($required_columns, $columns);
            
            if (!empty($missing_columns)) {
                echo '<p class="warning">Ürünler tablosunda eksik sütunlar:</p>';
                echo '<ul>';
                foreach ($missing_columns as $column) {
                    echo '<li>' . htmlspecialchars($column) . '</li>';
                    
                    // Eksik sütunları ekle
                    try {
                        switch ($column) {
                            case 'slug':
                                $db->exec("ALTER TABLE urunler ADD COLUMN slug VARCHAR(255) NOT NULL UNIQUE AFTER name");
                                echo '<p class="success">Slug sütunu eklendi.</p>';
                                break;
                            case 'description':
                                $db->exec("ALTER TABLE urunler ADD COLUMN description TEXT AFTER slug");
                                echo '<p class="success">Description sütunu eklendi.</p>';
                                break;
                            case 'category_id':
                                $db->exec("ALTER TABLE urunler ADD COLUMN category_id INT AFTER id");
                                echo '<p class="success">Category_id sütunu eklendi.</p>';
                                break;
                            case 'status':
                                $db->exec("ALTER TABLE urunler ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER image");
                                echo '<p class="success">Status sütunu eklendi.</p>';
                                break;
                        }
                    } catch (PDOException $e) {
                        echo '<p class="error">Sütun ekleme hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
                echo '</ul>';
            } else {
                echo '<p class="success">Ürünler tablosu sütunları tam.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">Ürünler tablosu kontrol hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    // Örnek veri ekle
    echo '<h3>Örnek Veri Ekleme</h3>';
    
    // Kategoriler tablosuna örnek veri ekle
    if (in_array('categories', $tables)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM categories");
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                echo '<p class="warning">Kategoriler tablosunda veri yok. Örnek veri ekleniyor...</p>';
                
                $db->exec("
                    INSERT INTO categories (name, slug, description, status) VALUES 
                    ('Matbaa Ürünleri', 'matbaa-urunleri', 'Profesyonel matbaa ürünleri', 1),
                    ('Promosyon Ürünleri', 'promosyon-urunleri', 'Kurumsal promosyon ürünleri', 1),
                    ('Dijital Baskı', 'dijital-baski', 'Yüksek kaliteli dijital baskı hizmetleri', 1),
                    ('Tabela ve Totem', 'tabela-ve-totem', 'Tabela ve totem çözümleri', 1)
                ");
                
                echo '<p class="success">Kategoriler tablosuna örnek veriler eklendi.</p>';
            } else {
                echo '<p class="success">Kategoriler tablosunda zaten veri var.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">Kategori veri ekleme hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    // Hizmetler tablosuna örnek veri ekle
    if (in_array('services', $tables)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM services");
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                echo '<p class="warning">Hizmetler tablosunda veri yok. Örnek veri ekleniyor...</p>';
                
                $db->exec("
                    INSERT INTO services (name, slug, description, feature1, feature2, feature3, status) VALUES 
                    ('Grafik Tasarım', 'grafik-tasarim', 'Profesyonel grafik tasarım hizmetleri', 'Logo ve kurumsal kimlik tasarımı', 'Broşür ve katalog tasarımı', 'Ambalaj tasarımı', 1),
                    ('Matbaa Hizmetleri', 'matbaa-hizmetleri', 'Yüksek kaliteli matbaa hizmetleri', 'Ofset baskı', 'Dijital baskı', 'Serigrafi baskı', 1),
                    ('Web Tasarım', 'web-tasarim', 'Modern ve responsive web tasarım hizmetleri', 'Kurumsal web siteleri', 'E-ticaret siteleri', 'SEO uyumlu tasarım', 1),
                    ('Sosyal Medya Yönetimi', 'sosyal-medya-yonetimi', 'Etkili sosyal medya yönetimi', 'İçerik üretimi', 'Kampanya yönetimi', 'Analiz ve raporlama', 1)
                ");
                
                echo '<p class="success">Hizmetler tablosuna örnek veriler eklendi.</p>';
            } else {
                echo '<p class="success">Hizmetler tablosunda zaten veri var.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">Hizmet veri ekleme hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    // Ürünler tablosuna örnek veri ekle
    if (in_array('urunler', $tables) && in_array('categories', $tables)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM urunler");
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                echo '<p class="warning">Ürünler tablosunda veri yok. Örnek veri ekleniyor...</p>';
                
                // Kategori ID'lerini al
                $stmt = $db->query("SELECT id FROM categories LIMIT 4");
                $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($categories) > 0) {
                    $category_id1 = $categories[0] ?? 1;
                    $category_id2 = $categories[1] ?? 1;
                    $category_id3 = $categories[2] ?? 1;
                    $category_id4 = $categories[3] ?? 1;
                    
                    $db->exec("
                        INSERT INTO urunler (category_id, name, slug, description, status) VALUES 
                        ($category_id1, 'Kartvizit', 'kartvizit', 'Profesyonel kartvizit baskı hizmeti', 1),
                        ($category_id1, 'Broşür', 'brosur', 'Yüksek kaliteli broşür baskı hizmeti', 1),
                        ($category_id2, 'Kalem', 'kalem', 'Kurumsal promosyon kalem', 1),
                        ($category_id2, 'Ajanda', 'ajanda', 'Özel tasarım ajanda', 1),
                        ($category_id3, 'Poster Baskı', 'poster-baski', 'Büyük boyutlu poster baskı hizmeti', 1),
                        ($category_id3, 'Afiş Baskı', 'afis-baski', 'Yüksek çözünürlüklü afiş baskı', 1),
                        ($category_id4, 'Işıklı Tabela', 'isikli-tabela', 'LED aydınlatmalı tabela', 1),
                        ($category_id4, 'Totem', 'totem', 'Özel tasarım totem', 1)
                    ");
                    
                    echo '<p class="success">Ürünler tablosuna örnek veriler eklendi.</p>';
                } else {
                    echo '<p class="warning">Kategori bulunamadığı için ürün eklenemedi.</p>';
                }
            } else {
                echo '<p class="success">Ürünler tablosunda zaten veri var.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">Ürün veri ekleme hatası: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
} catch (PDOException $e) {
    echo '<p class="error">Genel hata: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '<p><a href="check_tables.php" class="fix-button">Tabloları Kontrol Et</a> <a href="index.php" class="fix-button">Ana Sayfaya Dön</a></p>';
?> 