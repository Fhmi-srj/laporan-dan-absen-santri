-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 26, 2025 at 12:57 AM
-- Server version: 10.5.29-MariaDB
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `diantar2_daftar_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

DROP TABLE IF EXISTS `pendaftaran`;

CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL,
  `no_registrasi` varchar(10) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `lembaga` enum('SMP NU BP','MA ALHIKAM') NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `jumlah_saudara` int(11) DEFAULT 0,
  `no_kk` varchar(20) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kota_kab` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kelurahan_desa` varchar(100) DEFAULT NULL,
  `asal_sekolah` varchar(100) DEFAULT NULL,
  `prestasi` varchar(200) DEFAULT NULL,
  `tingkat_prestasi` enum('KABUPATEN','PROVINSI','NASIONAL') DEFAULT NULL,
  `juara` enum('1','2','3') DEFAULT NULL,
  `file_sertifikat` varchar(255) DEFAULT NULL,
  `pip_pkh` varchar(50) DEFAULT NULL,
  `status_mukim` enum('PONDOK PP MAMBAUL HUDA','PONDOK SELAIN PP MAMBAUL HUDA','TIDAK PONDOK') NOT NULL,
  `sumber_info` varchar(50) DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `tempat_lahir_ayah` varchar(50) DEFAULT NULL,
  `tanggal_lahir_ayah` date DEFAULT NULL,
  `nik_ayah` varchar(20) DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `penghasilan_ayah` varchar(20) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `tempat_lahir_ibu` varchar(50) DEFAULT NULL,
  `tanggal_lahir_ibu` date DEFAULT NULL,
  `nik_ibu` varchar(20) DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `penghasilan_ibu` varchar(20) DEFAULT NULL,
  `no_hp_wali` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `file_ktp_ortu` varchar(255) DEFAULT NULL,
  `file_akta` varchar(255) DEFAULT NULL,
  `file_ijazah` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `catatan_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `no_registrasi`, `nama`, `lembaga`, `nisn`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `jumlah_saudara`, `no_kk`, `nik`, `alamat`, `provinsi`, `kota_kab`, `kecamatan`, `kelurahan_desa`, `asal_sekolah`, `prestasi`, `tingkat_prestasi`, `juara`, `file_sertifikat`, `pip_pkh`, `status_mukim`, `sumber_info`, `nama_ayah`, `tempat_lahir_ayah`, `tanggal_lahir_ayah`, `nik_ayah`, `pekerjaan_ayah`, `penghasilan_ayah`, `nama_ibu`, `tempat_lahir_ibu`, `tanggal_lahir_ibu`, `nik_ibu`, `pekerjaan_ibu`, `penghasilan_ibu`, `no_hp_wali`, `password`, `file_kk`, `file_ktp_ortu`, `file_akta`, `file_ijazah`, `status`, `catatan_admin`, `catatan_updated_at`, `created_at`) VALUES
(1, '001', 'SOFA HILDA AYU MAULIDA', 'SMP NU BP', '0142465055', 'PEKALONGAN', '2014-05-11', 'P', 1, '3326181712080002', '3375035105140002', 'DUSUN PANGKAH, RT. 003/RW. 002', 'JAWA TENGAH', 'KABUPATEN PEKALONGAN', 'KARANGDADAP', 'PANGKAH', 'SD MUHAMMADIYAH PANGKAH', '', NULL, NULL, '', '', 'PONDOK PP MAMBAUL HUDA', 'KELUARGA', 'WARTO', 'PEKALONGAN', '1962-12-12', '3326181212620004', 'BURUH HARIAN LEPAS', '< 1 JUTA', 'UMI FADHILAH', 'PEKALONGAN', '1975-10-18', '3326185810750001', 'KARYAWAN SWASTA', '1-3 JUTA', '+6288216683867', '$2y$10$KNgNWdvM9c1VXGPqYtnJaOBtyh/4XDXB/vNYtqfsIPtr2KWR0GJ6q', '', '', '', '', 'pending', NULL, NULL, '2025-12-22 13:48:38'),
(2, '002', 'FATAN FIRMANSYAH', 'SMP NU BP', '', 'PEKALONGAN', '2013-01-11', 'L', 0, '3326131812120006', '3326131101130004', 'RT/RW 001/001', 'JAWA TENGAH', 'KABUPATEN PEKALONGAN', 'KEDUNGWUNI', 'TANGKIL TENGAH', '', '', NULL, NULL, '', '', 'PONDOK PP MAMBAUL HUDA', 'TEMAN', 'NUR AMINAH', 'PEKALONGAN', '1989-12-01', '33261358108900021', 'BURUH', '< 1 JUTA', 'NUR AMINAH', 'PEKALONGAN', '1989-12-01', '33261358108900021', 'BURUH', '< 1 JUTA', '+6285956403558', '$2y$10$e2xaWLOjroyQfwTTPEiMPeyGNa/LeSdlGib3OB9ZTZSjuUL62DTAG', '57_file_kk_1766473602.pdf', '', '', '', 'pending', NULL, NULL, '2025-12-23 07:03:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `unique_phone` (`no_hp_wali`) USING BTREE,
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
