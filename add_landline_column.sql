-- Settings tablosuna site_landline sütunu ekleme
ALTER TABLE settings ADD COLUMN site_landline VARCHAR(20) DEFAULT NULL;

-- Contacts tablosuna landline sütunu ekleme
ALTER TABLE contacts ADD COLUMN landline VARCHAR(20) DEFAULT NULL;

-- Eğer contact_landline sütunu da gerekiyorsa
ALTER TABLE settings ADD COLUMN contact_landline VARCHAR(20) DEFAULT NULL;

-- Veritabanında değişiklikleri kaydetme
COMMIT; 