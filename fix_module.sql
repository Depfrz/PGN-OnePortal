-- Script untuk memperbaiki Modul Dokumen Kedaluwarsa
-- Silakan jalankan script ini di phpMyAdmin atau tool database Anda

-- 1. Pastikan modul ada (INSERT IGNORE agar tidak duplikat jika sudah ada)
INSERT IGNORE INTO `modules` (`name`, `slug`, `url`, `icon`, `group`, `status`, `order`, `created_at`, `updated_at`) 
VALUES 
('Dokumen Kedaluwarsa', 'dokumen-kedaluwarsa', '/buku-saku/expired', 'clock', 'Buku Saku', 1, 4, NOW(), NOW());

-- 2. Update urutan modul lain agar rapi
UPDATE `modules` SET `order` = 1 WHERE `slug` = 'buku-saku';
UPDATE `modules` SET `order` = 2 WHERE `slug` = 'buku-saku-favorites';
UPDATE `modules` SET `order` = 3 WHERE `slug` = 'buku-saku-approval';
UPDATE `modules` SET `order` = 4 WHERE `slug` = 'dokumen-kedaluwarsa';
UPDATE `modules` SET `order` = 5 WHERE `slug` = 'buku-saku-history';
UPDATE `modules` SET `order` = 6 WHERE `slug` = 'buku-saku-upload';
