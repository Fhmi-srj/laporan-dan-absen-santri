-- =====================================================
-- DATABASE SCHEMA: Laporan Santri
-- Converted from Laravel to PHP Murni + MySQL
-- With Complete Dummy Data
-- =====================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `diantar2_absen` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `diantar2_absen`;

-- =====================================================
-- TABLE: users
-- =====================================================
DROP TABLE IF EXISTS `user_devices`;
DROP TABLE IF EXISTS `catatan_aktivitas`;
DROP TABLE IF EXISTS `attendances`;
DROP TABLE IF EXISTS `siswa`;
DROP TABLE IF EXISTS `jadwal_absens`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT NULL,
    `role` ENUM('admin', 'karyawan', 'pengurus', 'guru', 'keamanan', 'kesehatan') NOT NULL DEFAULT 'karyawan',
    `foto` VARCHAR(255) DEFAULT 'profile.jpg',
    `phone` VARCHAR(20) NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default users for all 6 roles (password: password)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `created_at`, `updated_at`) VALUES
('Administrator', 'admin@mambaul-huda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567890', 'Kantor Pondok Pesantren', NOW(), NOW()),
('Karyawan Demo', 'karyawan@mambaul-huda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'karyawan', '081234567891', 'Jl. Karyawan No. 1', NOW(), NOW()),
('Pengurus Demo', 'pengurus@mambaul-huda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pengurus', '081234567892', 'Jl. Pengurus No. 2', NOW(), NOW()),
('Ustadz Ahmad', 'guru@mambaul-huda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru', '081234567893', 'Asrama Ustadz Blok A', NOW(), NOW()),
('Pak Satpam', 'keamanan@mambaul-huda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'keamanan', '081234567894', 'Pos Keamanan', NOW(), NOW()),
('Ibu Dokter', 'kesehatan@mambaul-huda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kesehatan', '081234567895', 'Klinik Pondok', NOW(), NOW());

-- =====================================================
-- TABLE: user_devices
-- =====================================================
CREATE TABLE `user_devices` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `device_fingerprint` VARCHAR(255) NOT NULL,
    `device_name` VARCHAR(255) NULL,
    `last_used_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: siswa (20 siswa sample)
-- =====================================================
CREATE TABLE `siswa` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nama_lengkap` VARCHAR(255) NOT NULL,
    `nomor_induk` VARCHAR(50) NOT NULL UNIQUE,
    `no_kartu_rfid` VARCHAR(50) NULL UNIQUE,
    `kelas` VARCHAR(50) NOT NULL,
    `alamat` TEXT NULL,
    `no_wa` VARCHAR(20) NULL,
    `no_wa_wali` VARCHAR(20) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `siswa` (`nama_lengkap`, `nomor_induk`, `no_kartu_rfid`, `kelas`, `alamat`, `no_wa`, `no_wa_wali`) VALUES
-- Kelas X-A
('Ahmad Fauzi', '2024001', 'RFID001', 'X-A', 'Jl. Merdeka No. 1, Kediri', '081111000001', '081222000001'),
('Budi Santoso', '2024002', 'RFID002', 'X-A', 'Jl. Sudirman No. 12, Kediri', '081111000002', '081222000002'),
('Candra Wijaya', '2024003', 'RFID003', 'X-A', 'Jl. Pahlawan No. 5, Kediri', '081111000003', '081222000003'),
('Dimas Pratama', '2024004', 'RFID004', 'X-A', 'Jl. Diponegoro No. 8, Kediri', '081111000004', '081222000004'),
('Eko Purnomo', '2024005', 'RFID005', 'X-A', 'Jl. Ahmad Yani No. 3, Kediri', '081111000005', '081222000005'),
-- Kelas X-B
('Fajar Nugroho', '2024006', 'RFID006', 'X-B', 'Jl. Gatot Subroto No. 7, Pare', '081111000006', '081222000006'),
('Gilang Ramadhan', '2024007', 'RFID007', 'X-B', 'Jl. Imam Bonjol No. 9, Pare', '081111000007', '081222000007'),
('Hadi Susanto', '2024008', 'RFID008', 'X-B', 'Jl. Cut Nyak Dien No. 2, Pare', '081111000008', '081222000008'),
('Irfan Hakim', '2024009', 'RFID009', 'X-B', 'Jl. RA Kartini No. 15, Pare', '081111000009', '081222000009'),
('Joko Widodo', '2024010', 'RFID010', 'X-B', 'Jl. Hasanuddin No. 4, Pare', '081111000010', '081222000010'),
-- Kelas XI-A
('Kurniawan Adi', '2024011', 'RFID011', 'XI-A', 'Jl. Veteran No. 11, Nganjuk', '081111000011', '081222000011'),
('Lukman Hakim', '2024012', 'RFID012', 'XI-A', 'Jl. Pemuda No. 6, Nganjuk', '081111000012', '081222000012'),
('Muhammad Rizky', '2024013', 'RFID013', 'XI-A', 'Jl. Pelajar No. 13, Nganjuk', '081111000013', '081222000013'),
('Naufal Ahmad', '2024014', 'RFID014', 'XI-A', 'Jl. Mahasiswa No. 20, Nganjuk', '081111000014', '081222000014'),
('Oscar Putra', '2024015', 'RFID015', 'XI-A', 'Jl. Dosen No. 17, Nganjuk', '081111000015', '081222000015'),
-- Kelas XI-B
('Prasetyo Adi', '2024016', 'RFID016', 'XI-B', 'Jl. Guru No. 25, Jombang', '081111000016', '081222000016'),
('Qodir Rahman', '2024017', 'RFID017', 'XI-B', 'Jl. Santri No. 30, Jombang', '081111000017', '081222000017'),
('Rudi Hermawan', '2024018', 'RFID018', 'XI-B', 'Jl. Kyai No. 22, Jombang', '081111000018', '081222000018'),
('Samsul Arifin', '2024019', 'RFID019', 'XI-B', 'Jl. Pesantren No. 14, Jombang', '081111000019', '081222000019'),
('Taufik Hidayat', '2024020', 'RFID020', 'XI-B', 'Jl. Masjid No. 18, Jombang', '081111000020', '081222000020');

-- =====================================================
-- TABLE: jadwal_absens
-- =====================================================
CREATE TABLE `jadwal_absens` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(50) NOT NULL UNIQUE,
    `start_time` TIME NOT NULL,
    `scheduled_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `late_tolerance_minutes` INT DEFAULT 15,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jadwal_absens` (`name`, `type`, `start_time`, `scheduled_time`, `end_time`, `late_tolerance_minutes`) VALUES
('Absen Masuk', 'clock_in', '05:30:00', '06:00:00', '08:00:00', 15),
('Absen Pulang', 'clock_out', '15:00:00', '16:00:00', '18:00:00', 0);

-- =====================================================
-- TABLE: attendances
-- =====================================================
CREATE TABLE `attendances` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `jadwal_id` BIGINT UNSIGNED NULL,
    `attendance_date` DATE NOT NULL,
    `attendance_time` TIME NOT NULL,
    `status` ENUM('hadir', 'terlambat', 'absen', 'izin', 'sakit', 'pulang') DEFAULT 'hadir',
    `minutes_late` INT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_absens`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dummy Attendance Data (7 hari terakhir)
INSERT INTO `attendances` (`user_id`, `jadwal_id`, `attendance_date`, `attendance_time`, `status`, `minutes_late`) VALUES
-- Hari ini (sebagian)
(1, 1, CURDATE(), '05:55:00', 'hadir', 0),
(2, 1, CURDATE(), '06:05:00', 'hadir', 0),
(3, 1, CURDATE(), '06:20:00', 'terlambat', 20),
(4, 1, CURDATE(), '05:58:00', 'hadir', 0),
(5, 1, CURDATE(), '06:30:00', 'terlambat', 30),
(6, 1, CURDATE(), '05:50:00', 'hadir', 0),
(7, 1, CURDATE(), '06:02:00', 'hadir', 0),
(8, 1, CURDATE(), '06:10:00', 'hadir', 0),
-- Kemarin
(1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '05:55:00', 'hadir', 0),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '06:00:00', 'hadir', 0),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '05:58:00', 'hadir', 0),
(4, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '06:25:00', 'terlambat', 25),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '05:50:00', 'hadir', 0),
(6, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '05:52:00', 'hadir', 0),
(7, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '06:05:00', 'hadir', 0),
(8, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '05:59:00', 'hadir', 0),
(9, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '06:18:00', 'terlambat', 18),
(10, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '05:48:00', 'hadir', 0),
-- 2 hari lalu
(1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '05:58:00', 'hadir', 0),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '06:15:00', 'terlambat', 15),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '05:55:00', 'hadir', 0),
(4, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '05:50:00', 'hadir', 0),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '06:00:00', 'hadir', 0),
(11, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '05:45:00', 'hadir', 0),
(12, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '05:55:00', 'hadir', 0),
(13, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '06:22:00', 'terlambat', 22),
-- 3 hari lalu
(1, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '05:52:00', 'hadir', 0),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '05:58:00', 'hadir', 0),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '06:00:00', 'hadir', 0),
(4, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '06:35:00', 'terlambat', 35),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '05:55:00', 'hadir', 0),
(6, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '05:48:00', 'hadir', 0),
(14, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '05:50:00', 'hadir', 0),
(15, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '06:02:00', 'hadir', 0),
-- 4-6 hari lalu (sample)
(1, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), '05:55:00', 'hadir', 0),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), '06:00:00', 'hadir', 0),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), '06:28:00', 'terlambat', 28),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '05:50:00', 'hadir', 0),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '05:55:00', 'hadir', 0),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '05:58:00', 'hadir', 0),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), '05:48:00', 'hadir', 0),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), '06:18:00', 'terlambat', 18),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), '05:52:00', 'hadir', 0);

-- =====================================================
-- TABLE: catatan_aktivitas
-- =====================================================
CREATE TABLE `catatan_aktivitas` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `siswa_id` BIGINT UNSIGNED NOT NULL,
    `kategori` VARCHAR(50) NOT NULL,
    `judul` VARCHAR(255) NULL,
    `keterangan` TEXT NULL,
    `status_sambangan` VARCHAR(50) NULL,
    `status_kegiatan` VARCHAR(50) NULL,
    `tanggal` DATETIME NULL,
    `tanggal_selesai` DATETIME NULL,
    `foto_dokumen_1` VARCHAR(255) NULL,
    `foto_dokumen_2` VARCHAR(255) NULL,
    `dibuat_oleh` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dummy Catatan Aktivitas
INSERT INTO `catatan_aktivitas` (`siswa_id`, `kategori`, `judul`, `keterangan`, `status_sambangan`, `status_kegiatan`, `tanggal`, `tanggal_selesai`, `dibuat_oleh`) VALUES
-- Sakit
(1, 'sakit', 'Demam', 'Demam tinggi 39°C, perlu istirahat di klinik', NULL, 'Sudah Diperiksa', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 6),
(5, 'sakit', 'Sakit Perut', 'Mual dan sakit perut, mungkin masuk angin', NULL, 'Sudah Diperiksa', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 6),
(8, 'sakit', 'Flu', 'Flu dan batuk-batuk, diberi obat', NULL, 'Belum Diperiksa', NOW(), NULL, 6),
-- Izin Keluar
(2, 'izin_keluar', 'Beli Buku', 'Izin keluar untuk membeli buku pelajaran di toko', NULL, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR), 4),
(7, 'izin_keluar', 'Ke Bank', 'Urusan transfer biaya sekolah', NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 4),
-- Izin Pulang
(3, 'izin_pulang', 'Acara Keluarga', 'Ada acara pernikahan kakak', NULL, NULL, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 4 DAY), INTERVAL 2 DAY), 1),
(10, 'izin_pulang', 'Orang Tua Sakit', 'Ibu masuk rumah sakit, perlu menjenguk', NULL, NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 1),
-- Sambangan
(4, 'sambangan', 'Kunjungan Orang Tua', 'Orang tua berkunjung membawakan barang', 'Orang Tua', NULL, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 5),
(6, 'sambangan', 'Kunjungan Kakak', 'Kakak menjenguk dan memberikan uang saku', 'Keluarga', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 5),
(12, 'sambangan', 'Kunjungan Teman', 'Teman lama berkunjung memberikan oleh-oleh', 'Teman', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), 5),
-- Pelanggaran
(9, 'pelanggaran', 'Terlambat Sholat', 'Terlambat sholat subuh berjamaah 3x dalam seminggu', NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), NULL, 4),
(11, 'pelanggaran', 'Tidak Piket', 'Tidak melaksanakan tugas piket kamar', NULL, NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), NULL, 4),
-- Paket
(13, 'paket', 'Paket dari Orang Tua', 'Paket berisi makanan dan pakaian', NULL, NULL, NOW(), NULL, 5),
(14, 'paket', 'Paket Buku', 'Paket berisi buku-buku pelajaran tambahan', NULL, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, 5),
(15, 'paket', 'Paket Obat', 'Paket berisi vitamin dan suplemen', NULL, NULL, DATE_SUB(NOW(), INTERVAL 4 DAY), NULL, 5),
-- Hafalan
(16, 'hafalan', 'Juz 30 Selesai', 'Berhasil menyelesaikan hafalan Juz 30 dengan baik', NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 4),
(17, 'hafalan', 'Juz 29 - Setengah', 'Sudah menghafal setengah dari Juz 29', NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), NULL, 4),
(18, 'hafalan', 'Muroja\'ah Juz 30', 'Muroja\'ah hafalan Juz 30, masih ada beberapa yang perlu diperbaiki', NULL, NULL, NOW(), NULL, 4);

-- =====================================================
-- TABLE: settings
-- =====================================================
CREATE TABLE `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`, `value`) VALUES
('app_name', 'Laporan Santri'),
('school_name', 'Pondok Pesantren Mambaul Huda'),
('school_address', 'Jl. Pesantren No. 123, Kediri, Jawa Timur'),
('school_phone', '0354-123456'),
('wa_api_url', 'http://serverwa.hello-inv.com/send-message'),
('wa_api_key', 'VbM1epmqMKqrztVrWpd1YquAboWWFa'),
('wa_sender', '6282131871383'),
('latitude', '-7.8166'),
('longitude', '112.0148'),
('radius_meters', '100');
