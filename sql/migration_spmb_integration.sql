-- =====================================================
-- MIGRATION: Integrasi SPMB ke Website Utama
-- Tabel siswa diubah untuk referensi ke spmb_db.pendaftaran
-- =====================================================

-- Backup tabel siswa lama (jika ada data)
CREATE TABLE IF NOT EXISTS `siswa_backup` AS SELECT * FROM `siswa`;

-- Hapus foreign key constraints yang ada
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tabel siswa lama
DROP TABLE IF EXISTS `siswa`;

-- =====================================================
-- TABLE: siswa (NEW STRUCTURE)
-- Struktur minimal yang mereferensikan spmb_db.pendaftaran
-- =====================================================
CREATE TABLE `siswa` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pendaftaran_id` INT NOT NULL COMMENT 'FK ke spmb_db.pendaftaran.id',
    `nomor_induk` VARCHAR(50) NULL UNIQUE COMMENT 'NIS yang diisi admin',
    `no_kartu_rfid` VARCHAR(50) NULL UNIQUE COMMENT 'RFID yang diisi admin',
    `kelas` VARCHAR(50) NULL COMMENT 'Kelas yang diisi admin',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pendaftaran_id` (`pendaftaran_id`),
    UNIQUE KEY `unique_pendaftaran` (`pendaftaran_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CATATAN PENTING:
-- 
-- Untuk mengakses data lengkap santri, gunakan query JOIN:
--
-- SELECT 
--     s.id,
--     s.nomor_induk,
--     s.no_kartu_rfid,
--     s.kelas,
--     p.*  -- Semua field dari pendaftaran SPMB
-- FROM siswa s
-- JOIN spmb_db.pendaftaran p ON s.pendaftaran_id = p.id
-- WHERE p.status = 'verified'
--
-- =====================================================
