/*
 Navicat Premium Dump SQL

 Source Server         : XAMPP_Connection
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : localhost:3306
 Source Schema         : spmb_db

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 22/12/2025 11:30:42
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for activity_log
-- ----------------------------
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NULL DEFAULT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `admin_id`(`admin_id` ASC) USING BTREE,
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of activity_log
-- ----------------------------
INSERT INTO `activity_log` VALUES (1, 1, 'LOGIN', 'Login berhasil', '::1', '2025-12-20 23:04:22');
INSERT INTO `activity_log` VALUES (2, 1, 'PASSWORD_CHANGE', 'Mengubah password admin', '::1', '2025-12-20 23:04:52');
INSERT INTO `activity_log` VALUES (3, 1, 'PROFILE_UPDATE', 'Mengubah profil admin', '::1', '2025-12-20 23:05:03');
INSERT INTO `activity_log` VALUES (4, 1, 'LOGIN', 'Login berhasil', '::1', '2025-12-21 00:52:08');
INSERT INTO `activity_log` VALUES (5, 1, 'UPDATE', 'Mengupdate data pendaftaran: Rerum rem eu lorem m', '::1', '2025-12-21 01:01:01');
INSERT INTO `activity_log` VALUES (6, 1, 'UPDATE', 'Mengupdate data pendaftaran: Rerum rem eu lorem m', '::1', '2025-12-21 01:02:07');
INSERT INTO `activity_log` VALUES (7, 1, 'DELETE', 'Menghapus pendaftaran: Ut et excepteur quia', '::1', '2025-12-21 01:02:31');
INSERT INTO `activity_log` VALUES (8, 1, 'DELETE', 'Menghapus pendaftaran: Rerum rem eu lorem m', '::1', '2025-12-21 01:49:45');

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES (1, 'AdmFahmi', '$2y$10$r4/XCDEZ2JmNvtoCssc8e.WFsBA60FFv.eN1qCKOVyUkv4yOQHSDu', 'Fahmi Muhammad Sirojul Munir', '2025-12-20 22:36:31');

-- ----------------------------
-- Table structure for beasiswa
-- ----------------------------
DROP TABLE IF EXISTS `beasiswa`;
CREATE TABLE `beasiswa`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `jenis` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `syarat` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `benefit` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `urutan` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of beasiswa
-- ----------------------------
INSERT INTO `beasiswa` VALUES (1, 'Tahfidz', 'Penghafal Al-Quran', 'Hafal 1-5 Juz', 'Gratis SPP 1 Bulan', 1);
INSERT INTO `beasiswa` VALUES (2, 'Tahfidz', 'Penghafal Al-Quran', 'Hafal 6-10 Juz', 'Gratis SPP 2 Bulan', 2);
INSERT INTO `beasiswa` VALUES (3, 'Tahfidz', 'Penghafal Al-Quran', 'Hafal 11-20 Juz', 'Gratis SPP 3 Bulan', 3);
INSERT INTO `beasiswa` VALUES (4, 'Tahfidz', 'Penghafal Al-Quran', 'Hafal 21-30 Juz', 'Gratis SPP 6 Bulan', 4);
INSERT INTO `beasiswa` VALUES (5, 'Akademik', 'Berdasarkan Nilai Rapor', 'Nilai 90-100', 'Gratis SPP 3 Bulan', 5);
INSERT INTO `beasiswa` VALUES (6, 'Akademik', 'Berdasarkan Nilai Rapor', 'Nilai 80-89', 'Gratis SPP 2 Bulan', 6);
INSERT INTO `beasiswa` VALUES (7, 'Akademik', 'Berdasarkan Nilai Rapor', 'Nilai 70-79', 'Gratis SPP 1 Bulan', 7);
INSERT INTO `beasiswa` VALUES (8, 'Yatim/Piatu', 'Keringanan', 'Yatim/Piatu', 'Potongan 25% SPP', 8);
INSERT INTO `beasiswa` VALUES (9, 'Yatim/Piatu', 'Keringanan', 'Yatim Piatu', 'Potongan 50% SPP', 9);

-- ----------------------------
-- Table structure for biaya
-- ----------------------------
DROP TABLE IF EXISTS `biaya`;
CREATE TABLE `biaya`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `kategori` enum('PENDAFTARAN','DAFTAR_ULANG') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_item` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `biaya_pondok` int NULL DEFAULT 0,
  `biaya_smp` int NULL DEFAULT 0,
  `biaya_ma` int NULL DEFAULT 0,
  `urutan` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of biaya
-- ----------------------------
INSERT INTO `biaya` VALUES (1, 'PENDAFTARAN', 'Registrasi', 50000, 20000, 30000, 1);
INSERT INTO `biaya` VALUES (2, 'DAFTAR_ULANG', 'Baju Batik', 0, 65000, 90000, 1);
INSERT INTO `biaya` VALUES (3, 'DAFTAR_ULANG', 'Seragam Bawahan', 0, 0, 75000, 2);
INSERT INTO `biaya` VALUES (4, 'DAFTAR_ULANG', 'Jas Almamater', 170000, 0, 150000, 3);
INSERT INTO `biaya` VALUES (5, 'DAFTAR_ULANG', 'Kaos Olahraga', 0, 90000, 100000, 4);
INSERT INTO `biaya` VALUES (6, 'DAFTAR_ULANG', 'Badge Almamater', 0, 35000, 40000, 5);
INSERT INTO `biaya` VALUES (7, 'DAFTAR_ULANG', 'Buku Raport', 40000, 40000, 35000, 6);
INSERT INTO `biaya` VALUES (8, 'DAFTAR_ULANG', 'Infaq Bulan Pertama', 600000, 50000, 100000, 7);
INSERT INTO `biaya` VALUES (9, 'DAFTAR_ULANG', 'Kegiatan & Hari Besar', 150000, 50000, 380000, 8);
INSERT INTO `biaya` VALUES (10, 'DAFTAR_ULANG', 'Kitab/Buku Pelajaran', 100000, 0, 350000, 9);
INSERT INTO `biaya` VALUES (11, 'DAFTAR_ULANG', 'Perbaikan Asrama', 500000, 0, 0, 10);
INSERT INTO `biaya` VALUES (12, 'DAFTAR_ULANG', 'Kartu Santri', 40000, 0, 0, 11);
INSERT INTO `biaya` VALUES (13, 'DAFTAR_ULANG', 'Kebersihan', 150000, 0, 0, 12);
INSERT INTO `biaya` VALUES (14, 'DAFTAR_ULANG', 'Kalender', 50000, 0, 0, 13);

-- ----------------------------
-- Table structure for kontak
-- ----------------------------
DROP TABLE IF EXISTS `kontak`;
CREATE TABLE `kontak`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `lembaga` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `no_whatsapp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `link_wa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of kontak
-- ----------------------------
INSERT INTO `kontak` VALUES (1, 'SMP', 'Ust. Rino Mukti', '08123456789', 'http://wa.link/7svsg0');
INSERT INTO `kontak` VALUES (2, 'MA', 'Ust. Akrom Adabi', '0856 4164 7478', 'https://wa.link/ire9yv');
INSERT INTO `kontak` VALUES (3, 'PONPES', 'Ust. M. Kowi', '08123456790', 'https://wa.link/20sq3q');

-- ----------------------------
-- Table structure for pendaftaran
-- ----------------------------
DROP TABLE IF EXISTS `pendaftaran`;
CREATE TABLE `pendaftaran`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `lembaga` enum('SMP NU BP','MA ALHIKAM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nisn` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tempat_lahir` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tanggal_lahir` date NULL DEFAULT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `jumlah_saudara` int NULL DEFAULT 0,
  `no_kk` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `nik` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `asal_sekolah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `prestasi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tingkat_prestasi` enum('KABUPATEN','PROVINSI','NASIONAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `juara` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `file_sertifikat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `pip_pkh` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `status_mukim` enum('PONDOK PP MAMBAUL HUDA','PONDOK SELAIN PP MAMBAUL HUDA','TIDAK PONDOK') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `sumber_info` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `nama_ayah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tempat_lahir_ayah` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tanggal_lahir_ayah` date NULL DEFAULT NULL,
  `nik_ayah` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `penghasilan_ayah` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `nama_ibu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tempat_lahir_ibu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tanggal_lahir_ibu` date NULL DEFAULT NULL,
  `nik_ibu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `penghasilan_ibu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `no_hp_wali` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `file_kk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `file_ktp_ortu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `file_akta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `file_ijazah` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `status` enum('pending','verified','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique_phone`(`no_hp_wali` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 53 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pendaftaran
-- ----------------------------
INSERT INTO `pendaftaran` VALUES (3, 'Ahmad Fauzii', 'MA ALHIKAM', '0012345678901', 'Kudus', '2008-01-15', 'L', 2, '3319010101080001', '3319010101080001', 'Jl. Sunan Kudus No. 1, Kudus', 'MTs Negeri 1 Kudus', 'Juara MTQ', 'KABUPATEN', '1', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', '', 'Hasan Basri', 'Kudus', '1975-05-10', '3319010505750001', 'Pedagang', '3-5 Juta', 'Siti Aminah', 'Kudus', '1978-08-20', '3319012008780002', 'Ibu Rumah Tangga', '< 1 Juta', '081234567001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (4, 'Budi Santoso', 'SMP NU BP', '0012345678902', 'Jepara', '2010-02-20', 'L', 1, '3319010201100002', '3319010201100002', 'Jl. Raya Jepara No. 25, Jepara', 'SD Negeri 2 Jepara', NULL, NULL, NULL, NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Suparjo', 'Jepara', '1980-03-15', '3319011503800003', 'Nelayan', '1-3 Juta', 'Nur Halimah', 'Jepara', '1982-06-25', '3319012506820004', 'Pedagang', '1-3 Juta', '081234567002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (5, 'Citra Dewi', 'MA ALHIKAM', '0012345678903', 'Pati', '2008-03-10', 'P', 3, '3319010301080003', '3319010301080003', 'Jl. Ahmad Yani No. 50, Pati', 'MTs Salafiyah Pati', 'Hafidz 5 Juz', 'KABUPATEN', '2', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Alumni', 'Mukhtar', 'Pati', '1976-07-20', '3319012007760005', 'Petani', '< 1 Juta', 'Khoiriyah', 'Pati', '1979-09-15', '3319011509790006', 'Ibu Rumah Tangga', '< 1 Juta', '081234567003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (6, 'Dani Firmansyah', 'SMP NU BP', '0012345678904', 'Demak', '2010-04-25', 'L', 2, '3319010401100004', '3319010401100004', 'Jl. Sultan Fatah No. 10, Demak', 'SD Islam Terpadu Demak', 'Olimpiade Matematika', 'PROVINSI', '3', NULL, 'PIP', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Internet', 'Wahyudi', 'Demak', '1978-11-05', '3319010511780007', 'Wiraswasta', '3-5 Juta', 'Umi Kulsum', 'Demak', '1981-01-30', '3319013001810008', 'Guru', '1-3 Juta', '081234567004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (7, 'Eka Putri Rahayu', 'MA ALHIKAM', '0012345678905', 'Semarang', '2008-05-05', 'P', 1, '3319010501080005', '3319010501080005', 'Jl. Pandanaran No. 100, Semarang', 'MTs Negeri 2 Semarang', NULL, NULL, NULL, NULL, NULL, 'TIDAK PONDOK', 'Sosmed', 'Agus Salim', 'Semarang', '1974-12-10', '3319011012740009', 'PNS', '> 5 Juta', 'Sri Mulyani', 'Semarang', '1977-04-18', '3319011804770010', 'PNS', '3-5 Juta', '081234567005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (8, 'Fajar Nugroho', 'SMP NU BP', '0012345678906', 'Kendal', '2010-06-15', 'L', 4, '3319010601100006', '3319010601100006', 'Jl. Soekarno Hatta No. 75, Kendal', 'SD Muhammadiyah Kendal', 'Juara Pramuka', 'KABUPATEN', '1', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Teman', 'Bambang Sudarto', 'Kendal', '1979-08-25', '3319012508790011', 'Buruh', '< 1 Juta', 'Fatimah', 'Kendal', '1983-02-14', '3319011402830012', 'Ibu Rumah Tangga', '< 1 Juta', '081234567006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'rejected', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (9, 'Gita Anjani', 'MA ALHIKAM', '0012345678907', 'Batang', '2008-07-20', 'P', 2, '3319010701080007', '3319010701080007', 'Jl. Gatot Subroto No. 30, Batang', 'MTs Ma\'arif Batang', 'Pidato Bahasa Arab', 'PROVINSI', '2', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Sukardi', 'Batang', '1975-03-30', '3319013003750013', 'Pedagang', '1-3 Juta', 'Maryam', 'Batang', '1978-07-08', '3319010807780014', 'Pedagang', '1-3 Juta', '081234567007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (10, 'Hendra Wijaya', 'SMP NU BP', '0012345678908', 'Pekalongan', '2010-08-10', 'L', 1, '3319010801100008', '3319010801100008', 'Jl. Hayam Wuruk No. 55, Pekalongan', 'SD Negeri 1 Pekalongan', NULL, NULL, NULL, NULL, 'PIP', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Alumni', 'Suryanto', 'Pekalongan', '1977-06-12', '3319011206770015', 'Pengrajin Batik', '3-5 Juta', 'Sumiati', 'Pekalongan', '1980-10-05', '3319010510800016', 'Pengrajin Batik', '1-3 Juta', '081234567008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (11, 'Indah Permatasari', 'MA ALHIKAM', '0012345678909', 'Tegal', '2008-09-25', 'P', 3, '3319010901080009', '3319010901080009', 'Jl. Diponegoro No. 20, Tegal', 'MTs Negeri Tegal', 'Kaligrafi', 'NASIONAL', '1', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Abdullah', 'Tegal', '1973-04-20', '3319012004730017', 'Ustadz', '1-3 Juta', 'Aisyah', 'Tegal', '1976-12-15', '3319011512760018', 'Ibu Rumah Tangga', '< 1 Juta', '081234567009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (12, 'Joko Prasetyo', 'SMP NU BP', '0012345678910', 'Brebes', '2010-10-30', 'L', 2, '3319011001100010', '3319011001100010', 'Jl. Ahmad Dahlan No. 45, Brebes', 'SD Islam Brebes', 'Cerdas Cermat', 'KABUPATEN', '3', NULL, NULL, 'TIDAK PONDOK', 'Sosmed', 'Karno', 'Brebes', '1976-09-08', '3319010809760019', 'Petani', '< 1 Juta', 'Siti Romlah', 'Brebes', '1979-03-22', '3319012203790020', 'Petani', '< 1 Juta', '081234567010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (13, 'Kartika Sari', 'MA ALHIKAM', '0012345678911', 'Cilacap', '2008-11-12', 'P', 1, '3319011101080011', '3319011101080011', 'Jl. Sudirman No. 80, Cilacap', 'MTs Salafiyah Cilacap', NULL, NULL, NULL, NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Teman', 'Sutrisno', 'Cilacap', '1974-01-25', '3319012501740021', 'Nelayan', '1-3 Juta', 'Rusmini', 'Cilacap', '1977-05-18', '3319011805770022', 'Ibu Rumah Tangga', '< 1 Juta', '081234567011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (14, 'Lukman Hakim', 'SMP NU BP', '0012345678912', 'Banyumas', '2010-12-05', 'L', 3, '3319011201100012', '3319011201100012', 'Jl. Pahlawan No. 15, Banyumas', 'SD Negeri 3 Banyumas', 'Tahfidz 3 Juz', 'KABUPATEN', '2', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Imam Ghozali', 'Banyumas', '1978-07-14', '3319011407780023', 'Guru', '1-3 Juta', 'Zuhriyah', 'Banyumas', '1981-11-28', '3319012811810024', 'Guru', '1-3 Juta', '081234567012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (15, 'Mega Wulandari', 'MA ALHIKAM', '0012345678913', 'Purbalingga', '2008-01-18', 'P', 2, '3319010101080013', '3319010101080013', 'Jl. Kartini No. 60, Purbalingga', 'MTs Ma\'arif Purbalingga', 'Juara Nasyid', 'PROVINSI', '1', NULL, 'PIP', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Alumni', 'Mustofa', 'Purbalingga', '1975-10-30', '3319013010750025', 'Wiraswasta', '3-5 Juta', 'Nurul Huda', 'Purbalingga', '1978-02-14', '3319011402780026', 'Ibu Rumah Tangga', '< 1 Juta', '081234567013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (16, 'Naufal Rizki', 'SMP NU BP', '0012345678914', 'Kebumen', '2010-02-22', 'L', 1, '3319010201100014', '3319010201100014', 'Jl. Veteran No. 35, Kebumen', 'SD Islam Kebumen', NULL, NULL, NULL, NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Hadi Purnomo', 'Kebumen', '1979-04-05', '3319010504790027', 'PNS', '3-5 Juta', 'Dewi Sartika', 'Kebumen', '1982-08-19', '3319011908820028', 'PNS', '1-3 Juta', '081234567014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (17, 'Olivia Zahra', 'MA ALHIKAM', '0012345678915', 'Purworejo', '2008-03-08', 'P', 4, '3319010301080015', '3319010301080015', 'Jl. WR Supratman No. 90, Purworejo', 'MTs Negeri 1 Purworejo', 'Olimpiade Sains', 'NASIONAL', '2', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Sosmed', 'Ridwan', 'Purworejo', '1972-06-15', '3319011506720029', 'Dokter', '> 5 Juta', 'Laila', 'Purworejo', '1975-10-22', '3319012210750030', 'Bidan', '3-5 Juta', '081234567015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (18, 'Putra Aditya', 'SMP NU BP', '0012345678916', 'Wonosobo', '2010-04-14', 'L', 2, '3319010401100016', '3319010401100016', 'Jl. Bhayangkara No. 25, Wonosobo', 'SD Muhammadiyah Wonosobo', 'Pencak Silat', 'KABUPATEN', '1', NULL, NULL, 'TIDAK PONDOK', 'Teman', 'Sarjono', 'Wonosobo', '1977-12-28', '3319012812770031', 'Petani', '< 1 Juta', 'Yatimah', 'Wonosobo', '1980-04-10', '3319011004800032', 'Petani', '< 1 Juta', '081234567016', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'rejected', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (19, 'Qonita Azzahra', 'MA ALHIKAM', '0012345678917', 'Temanggung', '2008-05-20', 'P', 1, '3319010501080017', '3319010501080017', 'Jl. Pemuda No. 40, Temanggung', 'MTs Salafiyah Temanggung', NULL, NULL, NULL, NULL, 'PIP', 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Fathoni', 'Temanggung', '1976-08-17', '3319011708760033', 'Pedagang', '1-3 Juta', 'Sholihah', 'Temanggung', '1979-12-05', '3319010512790034', 'Pedagang', '1-3 Juta', '081234567017', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (20, 'Rizal Mahendra', 'SMP NU BP', '0012345678918', 'Magelang', '2010-06-28', 'L', 3, '3319010601100018', '3319010601100018', 'Jl. Tidar No. 70, Magelang', 'SD Negeri 1 Magelang', 'Futsal', 'PROVINSI', '3', NULL, NULL, 'PONDOK SELAIN PP MAMBAUL HUDA', 'Alumni', 'Sugeng', 'Magelang', '1975-02-20', '3319012002750035', 'TNI', '3-5 Juta', 'Murni', 'Magelang', '1978-06-15', '3319011506780036', 'Ibu Rumah Tangga', '< 1 Juta', '081234567018', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (21, 'Salma Khairunisa', 'MA ALHIKAM', '0012345678919', 'Boyolali', '2008-07-15', 'P', 2, '3319010701080019', '3319010701080019', 'Jl. Merdeka No. 55, Boyolali', 'MTs Negeri Boyolali', 'Tahfidz 10 Juz', 'NASIONAL', '1', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Zainal Abidin', 'Boyolali', '1973-11-08', '3319010811730037', 'Ustadz', '1-3 Juta', 'Khodijah', 'Boyolali', '1976-03-25', '3319012503760038', 'Ibu Rumah Tangga', '< 1 Juta', '081234567019', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (22, 'Taufik Hidayat', 'SMP NU BP', '0012345678920', 'Klaten', '2010-08-22', 'L', 1, '3319010801100020', '3319010801100020', 'Jl. Pemuda Selatan No. 12, Klaten', 'SD Islam Terpadu Klaten', NULL, NULL, NULL, NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Sosmed', 'Parman', 'Klaten', '1978-05-30', '3319013005780039', 'Wiraswasta', '3-5 Juta', 'Sunarni', 'Klaten', '1981-09-18', '3319011809810040', 'Wiraswasta', '1-3 Juta', '081234567020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (23, 'Ulfa Maulidiya', 'MA ALHIKAM', '0012345678921', 'Sukoharjo', '2008-09-10', 'P', 3, '3319010901080021', '3319010901080021', 'Jl. Slamet Riyadi No. 88, Sukoharjo', 'MTs Ma\'arif Sukoharjo', 'Juara MTQ', 'PROVINSI', '2', NULL, 'PIP', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Teman', 'Rochmat', 'Sukoharjo', '1974-07-12', '3319011207740041', 'Pedagang', '1-3 Juta', 'Muslimah', 'Sukoharjo', '1977-11-28', '3319012811770042', 'Ibu Rumah Tangga', '< 1 Juta', '081234567021', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (24, 'Vino Pratama', 'SMP NU BP', '0012345678922', 'Wonogiri', '2010-10-18', 'L', 2, '3319011001100022', '3319011001100022', 'Jl. Diponegoro No. 33, Wonogiri', 'SD Negeri 2 Wonogiri', 'Bulu Tangkis', 'KABUPATEN', '1', NULL, NULL, 'TIDAK PONDOK', 'Brosur', 'Darmanto', 'Wonogiri', '1976-03-05', '3319010503760043', 'Buruh', '< 1 Juta', 'Sumiyem', 'Wonogiri', '1979-07-22', '3319012207790044', 'Buruh', '< 1 Juta', '081234567022', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'rejected', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (25, 'Winda Astuti', 'MA ALHIKAM', '0012345678923', 'Karanganyar', '2008-11-25', 'P', 1, '3319011101080023', '3319011101080023', 'Jl. Lawu No. 45, Karanganyar', 'MTs Negeri Karanganyar', NULL, NULL, NULL, NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Alumni', 'Sumarno', 'Karanganyar', '1975-09-15', '3319011509750045', 'PNS', '3-5 Juta', 'Warsini', 'Karanganyar', '1978-01-30', '3319013001780046', 'PNS', '1-3 Juta', '081234567023', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (26, 'Xavier Putra', 'SMP NU BP', '0012345678924', 'Sragen', '2010-12-08', 'L', 4, '3319011201100024', '3319011201100024', 'Jl. Sukowati No. 60, Sragen', 'SD Muhammadiyah Sragen', 'Catur', 'PROVINSI', '1', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Hartono', 'Sragen', '1977-04-20', '3319012004770047', 'Wiraswasta', '> 5 Juta', 'Lestari', 'Sragen', '1980-08-12', '3319011208800048', 'Wiraswasta', '3-5 Juta', '081234567024', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (27, 'Yusuf Maulana', 'MA ALHIKAM', '0012345678925', 'Grobogan', '2008-01-30', 'L', 2, '3319010101080025', '3319010101080025', 'Jl. Ahmad Yani No. 77, Grobogan', 'MTs Salafiyah Grobogan', 'Pidato Bahasa Inggris', 'KABUPATEN', '3', NULL, 'PIP', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Sosmed', 'Slamet', 'Grobogan', '1974-10-08', '3319010810740049', 'Petani', '< 1 Juta', 'Kasiyem', 'Grobogan', '1977-02-15', '3319011502770050', 'Petani', '< 1 Juta', '081234567025', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (28, 'Zahra Aulia', 'SMP NU BP', '0012345678926', 'Blora', '2010-02-14', 'P', 1, '3319010201100026', '3319010201100026', 'Jl. Pemuda No. 22, Blora', 'SD Negeri 1 Blora', NULL, NULL, NULL, NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Teman', 'Bejo', 'Blora', '1978-06-22', '3319012206780051', 'Pedagang', '1-3 Juta', 'Dewi', 'Blora', '1981-10-05', '3319010510810052', 'Pedagang', '1-3 Juta', '081234567026', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (29, 'Aldi Nugroho', 'MA ALHIKAM', '0012345678927', 'Rembang', '2008-03-20', 'L', 3, '3319010301080027', '3319010301080027', 'Jl. Kartini No. 15, Rembang', 'MTs Negeri Rembang', 'Juara Tahfidz', 'NASIONAL', '1', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Kasno', 'Rembang', '1975-12-30', '3319013012750053', 'Nelayan', '1-3 Juta', 'Sutini', 'Rembang', '1978-04-18', '3319011804780054', 'Ibu Rumah Tangga', '< 1 Juta', '081234567027', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (30, 'Bella Safitri', 'SMP NU BP', '0012345678928', 'Tuban', '2010-04-05', 'P', 2, '3319010401100028', '3319010401100028', 'Jl. Basuki Rahmat No. 50, Tuban', 'SD Islam Tuban', 'Menari', 'KABUPATEN', '2', NULL, NULL, 'TIDAK PONDOK', 'Alumni', 'Ponijo', 'Tuban', '1976-08-15', '3319011508760055', 'Buruh', '< 1 Juta', 'Rusmiyati', 'Tuban', '1979-12-28', '3319012812790056', 'Buruh', '< 1 Juta', '081234567028', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'rejected', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (31, 'Cahyo Wibowo', 'MA ALHIKAM', '0012345678929', 'Lamongan', '2008-05-12', 'L', 1, '3319010501080029', '3319010501080029', 'Jl. Veteran No. 88, Lamongan', 'MTs Ma\'arif Lamongan', NULL, NULL, NULL, NULL, 'PIP', 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Suroso', 'Lamongan', '1977-02-10', '3319011002770057', 'Petani', '< 1 Juta', 'Sulastri', 'Lamongan', '1980-06-25', '3319012506800058', 'Petani', '< 1 Juta', '081234567029', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (32, 'Dina Rahmawati', 'SMP NU BP', '0012345678930', 'Bojonegoro', '2010-06-18', 'P', 3, '3319010601100030', '3319010601100030', 'Jl. Panglima Sudirman No. 30, Bojonegoro', 'SD Negeri 3 Bojonegoro', 'Basket', 'PROVINSI', '3', NULL, NULL, 'PONDOK SELAIN PP MAMBAUL HUDA', 'Sosmed', 'Paimin', 'Bojonegoro', '1974-11-22', '3319012211740059', 'Wiraswasta', '3-5 Juta', 'Sunarti', 'Bojonegoro', '1977-03-10', '3319011003770060', 'Wiraswasta', '1-3 Juta', '081234567030', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (33, 'Evan Saputra', 'MA ALHIKAM', '0012345678931', 'Ngawi', '2008-07-25', 'L', 2, '3319010701080031', '3319010701080031', 'Jl. Raya Ngawi No. 100, Ngawi', 'MTs Negeri Ngawi', 'Juara Robotik', 'NASIONAL', '2', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Teman', 'Sutopo', 'Ngawi', '1975-05-18', '3319011805750061', 'PNS', '3-5 Juta', 'Wartini', 'Ngawi', '1978-09-08', '3319010809780062', 'PNS', '1-3 Juta', '081234567031', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (34, 'Fitri Handayani', 'SMP NU BP', '0012345678932', 'Magetan', '2010-08-08', 'P', 1, '3319010801100032', '3319010801100032', 'Jl. Pahlawan No. 45, Magetan', 'SD Muhammadiyah Magetan', NULL, NULL, NULL, NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Waluyo', 'Magetan', '1978-01-25', '3319012501780063', 'Pedagang', '1-3 Juta', 'Mariyem', 'Magetan', '1981-05-12', '3319011205810064', 'Pedagang', '1-3 Juta', '081234567032', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (35, 'Gilang Ramadhan', 'MA ALHIKAM', '0012345678933', 'Madiun', '2008-09-15', 'L', 4, '3319010901080033', '3319010901080033', 'Jl. Urip Sumoharjo No. 20, Madiun', 'MTs Salafiyah Madiun', 'Sepak Bola', 'KABUPATEN', '1', NULL, 'PIP', 'TIDAK PONDOK', 'Alumni', 'Joko Susilo', 'Madiun', '1976-07-30', '3319013007760065', 'TNI', '3-5 Juta', 'Sri Wahyuni', 'Madiun', '1979-11-18', '3319011811790066', 'Ibu Rumah Tangga', '< 1 Juta', '081234567033', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (36, 'Hana Kusuma', 'SMP NU BP', '0012345678934', 'Nganjuk', '2010-10-22', 'P', 2, '3319011001100034', '3319011001100034', 'Jl. Gatot Subroto No. 65, Nganjuk', 'SD Negeri 1 Nganjuk', 'Tari Tradisional', 'PROVINSI', '2', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Solikin', 'Nganjuk', '1977-03-12', '3319011203770067', 'Guru', '1-3 Juta', 'Siti Khoiriyah', 'Nganjuk', '1980-07-28', '3319012807800068', 'Guru', '1-3 Juta', '081234567034', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (37, 'Irfan Maulana', 'MA ALHIKAM', '0012345678935', 'Jombang', '2008-11-05', 'L', 1, '3319011101080035', '3319011101080035', 'Jl. KH Wahid Hasyim No. 40, Jombang', 'MTs Negeri 1 Jombang', 'Hafidz 30 Juz', 'NASIONAL', '1', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Sosmed', 'Abdul Ghofur', 'Jombang', '1974-09-20', '3319012009740069', 'Ustadz', '1-3 Juta', 'Nur Aini', 'Jombang', '1977-01-15', '3319011501770070', 'Ibu Rumah Tangga', '< 1 Juta', '081234567035', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (38, 'Julia Permata', 'SMP NU BP', '0012345678936', 'Mojokerto', '2010-12-12', 'P', 3, '3319011201100036', '3319011201100036', 'Jl. Bhayangkara No. 80, Mojokerto', 'SD Islam Mojokerto', NULL, NULL, NULL, NULL, NULL, 'PONDOK SELAIN PP MAMBAUL HUDA', 'Teman', 'Dwi Santoso', 'Mojokerto', '1978-05-08', '3319010805780071', 'Wiraswasta', '3-5 Juta', 'Yanti', 'Mojokerto', '1981-09-25', '3319012509810072', 'Wiraswasta', '1-3 Juta', '081234567036', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'rejected', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (39, 'Kevin Ardiansyah', 'MA ALHIKAM', '0012345678937', 'Sidoarjo', '2008-01-08', 'L', 2, '3319010101080037', '3319010101080037', 'Jl. Raya Sidoarjo No. 55, Sidoarjo', 'MTs Ma\'arif Sidoarjo', 'Juara Olimpiade Fisika', 'PROVINSI', '1', NULL, 'PIP', 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Agus Supriyanto', 'Sidoarjo', '1975-11-15', '3319011511750073', 'Dokter', '> 5 Juta', 'Diana', 'Sidoarjo', '1978-03-22', '3319012203780074', 'Apoteker', '3-5 Juta', '081234567037', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (40, 'Luna Maya', 'SMP NU BP', '0012345678938', 'Pasuruan', '2010-02-18', 'P', 1, '3319010201100038', '3319010201100038', 'Jl. Panglima Sudirman No. 70, Pasuruan', 'SD Negeri 2 Pasuruan', 'Menyanyi', 'KABUPATEN', '3', NULL, NULL, 'TIDAK PONDOK', 'Alumni', 'Sunarto', 'Pasuruan', '1976-06-28', '3319012806760075', 'Buruh', '< 1 Juta', 'Tuminem', 'Pasuruan', '1979-10-15', '3319011510790076', 'Buruh', '< 1 Juta', '081234567038', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (41, 'Muhammad Rifqi', 'MA ALHIKAM', '0012345678939', 'Probolinggo', '2008-03-25', 'L', 3, '3319010301080039', '3319010301080039', 'Jl. Soekarno Hatta No. 35, Probolinggo', 'MTs Negeri Probolinggo', NULL, NULL, NULL, NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Hamid', 'Probolinggo', '1977-08-10', '3319011008770077', 'Petani', '< 1 Juta', 'Siti Maryam', 'Probolinggo', '1980-12-05', '3319010512800078', 'Petani', '< 1 Juta', '081234567039', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (42, 'Nabila Putri', 'SMP NU BP', '0012345678940', 'Lumajang', '2010-04-30', 'P', 2, '3319010401100040', '3319010401100040', 'Jl. Alun-alun No. 25, Lumajang', 'SD Muhammadiyah Lumajang', 'Tahfidz 5 Juz', 'KABUPATEN', '2', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Sosmed', 'Suwarno', 'Lumajang', '1974-12-18', '3319011812740079', 'Pedagang', '1-3 Juta', 'Nur Hayati', 'Lumajang', '1977-04-08', '3319010804770080', 'Pedagang', '1-3 Juta', '081234567040', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (43, 'Oscar Firmansyah', 'MA ALHIKAM', '0012345678941', 'Bondowoso', '2008-05-15', 'L', 1, '3319010501080041', '3319010501080041', 'Jl. Letjen Suprapto No. 60, Bondowoso', 'MTs Salafiyah Bondowoso', 'Juara Qiroah', 'NASIONAL', '1', NULL, 'PIP', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Teman', 'Yakub', 'Bondowoso', '1975-03-25', '3319012503750081', 'Ustadz', '1-3 Juta', 'Halimah', 'Bondowoso', '1978-07-12', '3319011207780082', 'Ibu Rumah Tangga', '< 1 Juta', '081234567041', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (44, 'Putri Andini', 'SMP NU BP', '0012345678942', 'Situbondo', '2010-06-22', 'P', 4, '3319010601100042', '3319010601100042', 'Jl. PB Sudirman No. 90, Situbondo', 'SD Negeri 1 Situbondo', NULL, NULL, NULL, NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Darmo', 'Situbondo', '1976-09-30', '3319013009760083', 'Nelayan', '< 1 Juta', 'Ngatirah', 'Situbondo', '1979-01-18', '3319011801790084', 'Ibu Rumah Tangga', '< 1 Juta', '081234567042', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'rejected', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (45, 'Raka Aditya', 'MA ALHIKAM', '0012345678943', 'Banyuwangi', '2008-07-08', 'L', 2, '3319010701080043', '3319010701080043', 'Jl. Adi Sucipto No. 45, Banyuwangi', 'MTs Negeri Banyuwangi', 'Pencak Silat', 'PROVINSI', '2', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Alumni', 'Suyitno', 'Banyuwangi', '1977-01-15', '3319011501770085', 'Wiraswasta', '3-5 Juta', 'Sumirah', 'Banyuwangi', '1980-05-28', '3319012805800086', 'Wiraswasta', '1-3 Juta', '081234567043', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (46, 'Sinta Maharani', 'SMP NU BP', '0012345678944', 'Jember', '2010-08-15', 'P', 1, '3319010801100044', '3319010801100044', 'Jl. Gajahmada No. 30, Jember', 'SD Islam Jember', 'Menulis Puisi', 'KABUPATEN', '1', NULL, NULL, 'TIDAK PONDOK', 'Internet', 'Budiman', 'Jember', '1978-03-22', '3319012203780087', 'Guru', '1-3 Juta', 'Endang', 'Jember', '1981-07-10', '3319011007810088', 'Guru', '1-3 Juta', '081234567044', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (47, 'Tegar Prasetya', 'MA ALHIKAM', '0012345678945', 'Malang', '2008-09-22', 'L', 3, '3319010901080045', '3319010901080045', 'Jl. Ijen No. 100, Malang', 'MTs Ma\'arif Malang', NULL, NULL, NULL, NULL, 'PIP', 'PONDOK PP MAMBAUL HUDA', 'Sosmed', 'Subandi', 'Malang', '1975-07-18', '3319011807750089', 'PNS', '3-5 Juta', 'Lilik', 'Malang', '1978-11-25', '3319012511780090', 'PNS', '1-3 Juta', '081234567045', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (48, 'Umi Kulthum', 'SMP NU BP', '0012345678946', 'Batu', '2010-10-30', 'P', 2, '3319011001100046', '3319011001100046', 'Jl. Panglima Sudirman No. 55, Batu', 'SD Negeri 3 Batu', 'Hafidz 7 Juz', 'PROVINSI', '3', NULL, NULL, 'PONDOK SELAIN PP MAMBAUL HUDA', 'Teman', 'Surono', 'Batu', '1976-11-08', '3319010811760091', 'Pedagang', '1-3 Juta', 'Sutinah', 'Batu', '1979-03-15', '3319011503790092', 'Pedagang', '1-3 Juta', '081234567046', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (49, 'Vicky Setiawan', 'MA ALHIKAM', '0012345678947', 'Kediri', '2008-11-12', 'L', 1, '3319011101080047', '3319011101080047', 'Jl. Dhoho No. 75, Kediri', 'MTs Negeri 1 Kediri', 'Juara Debat', 'KABUPATEN', '2', NULL, 'PKH', 'PONDOK PP MAMBAUL HUDA', 'Brosur', 'Katimin', 'Kediri', '1974-05-20', '3319012005740093', 'Petani', '< 1 Juta', 'Sukarni', 'Kediri', '1977-09-08', '3319010809770094', 'Petani', '< 1 Juta', '081234567047', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'rejected', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (50, 'Widya Kusumawati', 'SMP NU BP', '0012345678948', 'Tulungagung', '2010-12-05', 'P', 3, '3319011201100048', '3319011201100048', 'Jl. Pahlawan No. 20, Tulungagung', 'SD Muhammadiyah Tulungagung', NULL, NULL, NULL, NULL, NULL, 'TIDAK PONDOK', 'Alumni', 'Jumadi', 'Tulungagung', '1975-09-25', '3319012509750095', 'Buruh', '< 1 Juta', 'Karsinah', 'Tulungagung', '1978-01-12', '3319011201780096', 'Buruh', '< 1 Juta', '081234567048', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (51, 'Yudha Pratama', 'MA ALHIKAM', '0012345678949', 'Blitar', '2008-01-18', 'L', 2, '3319010101080049', '3319010101080049', 'Jl. Merdeka No. 40, Blitar', 'MTs Salafiyah Blitar', 'Renang', 'NASIONAL', '1', NULL, 'PIP', 'PONDOK PP MAMBAUL HUDA', 'Internet', 'Suryadi', 'Blitar', '1977-04-30', '3319013004770097', 'TNI', '3-5 Juta', 'Tri Wahyuni', 'Blitar', '1980-08-18', '3319011808800098', 'Ibu Rumah Tangga', '< 1 Juta', '081234567049', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'verified', '2025-12-21 01:49:07');
INSERT INTO `pendaftaran` VALUES (52, 'Zaskia Adya', 'SMP NU BP', '0012345678950', 'Trenggalek', '2010-02-25', 'P', 1, '3319010201100050', '3319010201100050', 'Jl. Sukarno No. 65, Trenggalek', 'SD Negeri 1 Trenggalek', 'Baca Puisi', 'KABUPATEN', '3', NULL, NULL, 'PONDOK PP MAMBAUL HUDA', 'Sosmed', 'Purnomo', 'Trenggalek', '1978-07-12', '3319011207780099', 'Pedagang', '1-3 Juta', 'Kamsiyah', 'Trenggalek', '1981-11-28', '3319012811810100', 'Pedagang', '1-3 Juta', '081234567050', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'pending', '2025-12-21 01:49:07');

-- ----------------------------
-- Table structure for pengaturan
-- ----------------------------
DROP TABLE IF EXISTS `pengaturan`;
CREATE TABLE `pengaturan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `kunci` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nilai` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `keterangan` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `kunci`(`kunci` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pengaturan
-- ----------------------------
INSERT INTO `pengaturan` VALUES (1, 'status_pendaftaran', '1', 'Status pendaftaran: 1=Buka, 0=Tutup');
INSERT INTO `pengaturan` VALUES (2, 'tahun_ajaran', '2026/2027', 'Tahun ajaran aktif');
INSERT INTO `pengaturan` VALUES (3, 'link_pdf_biaya', 'https://s.id/biayapendaftaran', 'Link download PDF biaya');
INSERT INTO `pengaturan` VALUES (4, 'link_pdf_brosur', 'https://drive.google.com/file/d/1G9t4FnrnYo8amlPT9Y2OdLL-NQHDUIMB/view?usp=sharing', 'Link download PDF brosur');
INSERT INTO `pengaturan` VALUES (5, 'link_pdf_syarat', 'https://drive.google.com/file/d/1vbwof-2w_v2wzvosNYTzyE74EJqR0cEQ/view?usp=sharing', 'Link download PDF syarat');
INSERT INTO `pengaturan` VALUES (6, 'link_beasiswa', 'https://s.id/BeasiswaMAHAKAM', 'Link info beasiswa lengkap');
INSERT INTO `pengaturan` VALUES (7, 'gelombang_1_start', '2025-01-01', 'Tanggal mulai gelombang 1');
INSERT INTO `pengaturan` VALUES (8, 'gelombang_1_end', '2025-03-31', 'Tanggal selesai gelombang 1');
INSERT INTO `pengaturan` VALUES (9, 'gelombang_2_start', '2025-04-01', 'Tanggal mulai gelombang 2');
INSERT INTO `pengaturan` VALUES (10, 'gelombang_2_end', '2025-06-30', 'Tanggal selesai gelombang 2');

SET FOREIGN_KEY_CHECKS = 1;
