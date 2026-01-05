-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 04, 2026 at 09:21 PM
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
-- Database: `diantar2_absen`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `jadwal_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('clock_in','clock_out') NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'hadir',
  `attendance_date` date NOT NULL,
  `attendance_time` time NOT NULL,
  `minutes_late` int(11) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `user_id`, `jadwal_id`, `type`, `status`, `attendance_date`, `attendance_time`, `minutes_late`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'clock_in', 'terlambat', '2025-11-29', '17:55:01', 655, '-2.9990834831689', '104.78226915554', '2025-11-29 10:55:01', '2025-11-29 10:55:01');

-- --------------------------------------------------------

--
-- Table structure for table `catatan_aktivitas`
--

CREATE TABLE `catatan_aktivitas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status_sambangan` varchar(255) DEFAULT NULL,
  `status_kegiatan` varchar(100) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `foto_dokumen_1` varchar(255) DEFAULT NULL,
  `foto_dokumen_2` varchar(255) DEFAULT NULL,
  `dibuat_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catatan_aktivitas`
--

INSERT INTO `catatan_aktivitas` (`id`, `siswa_id`, `kategori`, `judul`, `keterangan`, `status_sambangan`, `status_kegiatan`, `tanggal`, `tanggal_selesai`, `foto_dokumen_1`, `foto_dokumen_2`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES
(1, 1, 'izin_keluar', 'tes', 'tes', 'Keluarga', NULL, '2025-11-17 04:29:00', '2025-12-06 20:33:00', NULL, NULL, 1, '2025-11-17 04:30:45', '2025-12-06 13:28:17'),
(4, 1, 'paket', 'A', 'ok', NULL, NULL, '2025-11-17 02:10:00', '2025-11-17 02:13:00', 'bukti_aktivitas/UoBI6c2pqg01XA3JCvsvCucoopNS2h2R1o6pPR4o.png', 'bukti_aktivitas/Js5tBAFUf1qHy0r1Izxhw8IpYoFr8ovPe8t6KiB8.png', 1, '2025-11-17 16:13:55', '2025-12-03 16:38:25'),
(5, 1, 'sakit', 'materi', 'ok', NULL, NULL, '2025-11-17 16:20:00', NULL, NULL, NULL, 1, '2025-11-17 16:21:12', '2025-12-01 03:41:41'),
(6, 1, 'izin_pulang', 'Urus Ijazah', NULL, NULL, 'Belum Dijenguk', '2025-12-01 18:13:00', NULL, NULL, NULL, 18, '2025-12-01 11:21:59', '2025-12-01 11:21:59'),
(7, 1, 'sakit', 'Demam', NULL, NULL, 'Belum Dijenguk', '2025-12-02 22:55:00', NULL, NULL, NULL, 18, '2025-12-02 15:55:40', '2025-12-02 15:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_absens`
--

CREATE TABLE `jadwal_absens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `scheduled_time` time NOT NULL,
  `end_time` time NOT NULL,
  `late_tolerance_minutes` int(11) DEFAULT 15,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwal_absens`
--

INSERT INTO `jadwal_absens` (`id`, `name`, `type`, `start_time`, `scheduled_time`, `end_time`, `late_tolerance_minutes`, `created_at`, `updated_at`) VALUES
(1, 'Masuk Pagi', 'clock_in', '06:00:00', '07:00:00', '18:03:52', 100, '2025-10-29 20:24:02', '2025-11-24 10:28:19'),
(2, 'Jamaah duhur', 'Duhur', '11:00:00', '12:00:00', '14:00:00', 100, '2025-11-01 15:12:54', '2025-11-08 00:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2025_07_16_124532_create_user_devices_table', 2),
(4, '2025_07_16_125325_create_attendances_table', 3),
(5, '2025_07_16_172007_add_minutes_late_to_attendances_table', 4),
(6, '2025_07_16_175122_add_status_to_attendances_table', 5),
(7, '2025_08_10_025916_create_siswa_table', 6),
(8, '2025_10_30_020909_create_jadwal_absens_table', 7),
(9, '2025_11_16_231006_create_catatan_aktivitas_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nomor_induk` varchar(255) NOT NULL,
  `no_kartu_rfid` varchar(255) DEFAULT NULL,
  `kelas` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `no_wa_wali` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nama_lengkap`, `nomor_induk`, `no_kartu_rfid`, `kelas`, `alamat`, `created_at`, `updated_at`, `no_wa`, `no_wa_wali`) VALUES
(1, 'juliansa', '45692', '0005407015', 'X', 'PPG', '2025-08-09 20:21:02', '2025-11-29 10:49:33', '085156390652', '085156390652');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','karyawan','pengurus','guru','keamanan','kesehatan') NOT NULL,
  `foto` varchar(255) NOT NULL DEFAULT 'profile.jpg',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `address`, `role`, `foto`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Juliansa', 'juliansa@gmail.com', '$2y$12$ZQOy4IkzvASJV2S6552LVuY8LayyFrJ/KIF7nsSbcyT78uxT5V8Se', 'jln kptn.idham pulau panggung', 'admin', 'profile.jpg', NULL, '2025-07-16 05:45:15', '2025-07-16 13:51:37'),
(2, 'Mandiri Motor SDL', 'mandiri@gmail.com', '$2y$12$0YBjdKyDG8NsxmNRrmqjpuUFXZI18t0NbZWEXwRjrURlgz/KkhwTm', 'Pulau Panggung Semende Darat Laut', 'admin', 'profile.jpg', NULL, '2025-07-16 05:45:16', '2025-07-16 13:52:48'),
(15, 'kowi', 'kowi@gmail.com', '$2y$12$juIOeMTtDe8ToxRGv/2rleAbsjqbV.0645Bq67HSmGBvTEJfga3SO', 'Tanah Abang', 'pengurus', 'profile.jpg', NULL, '2025-07-16 13:55:06', '2025-12-07 15:49:50'),
(16, 'Mizit', 'mizit@gmail.com', '$2y$12$oIdNGKIWo6wJ.z3zSo94CeCLDfMlYXjKb6s5h3HvEGAFL0WYhZoDC', 'Pulau Panggung', 'karyawan', 'profile.jpg', NULL, '2025-07-16 13:55:43', '2025-07-16 13:55:43'),
(17, 'testing', 'testing@gmail.com', '$2y$12$KZYpbGYK67vIw9dOweQDwe2YCgUxZFttJtB67ecS1/jT9NthZN8hC', NULL, 'karyawan', 'profile.jpg', NULL, '2025-07-16 14:57:46', '2025-07-16 14:57:46'),
(18, 'Nama Admin', 'admin@gmail.com', '$2y$12$P/6S4BnKaQNw6v9ohx.4fOP2vo32aahFay1M4Q1NaDU4/cWhEvd2C', 'Gg. B.Agam 1 No. 890, Bandung 55593, Sultra', 'admin', 'profile.jpg', NULL, '2025-10-29 19:12:14', '2025-11-01 14:48:16'),
(19, 'bidin', 'customer@gmail.com', '$2y$12$jhG/JhcoYQTl9EmihbY2xuaTHpZMDXA5S8X.0XlHyc/ImL2bR9YDa', 'Psr. Astana Anyar No. 394, Batu 35203, Jambi', 'admin', 'profile.jpg', NULL, '2025-10-29 19:12:14', '2025-12-07 15:46:19'),
(20, 'Akrom Adabi', 'akromadabi@gmail.com', '$2y$12$IGzpbMrPaHq1RV.Iun2B3ugXDm3HvbgXvIUPZbFNfxN.pwUCeDYz2', NULL, 'admin', 'profile.jpg', NULL, '2025-10-29 19:12:18', '2025-11-08 00:04:19'),
(21, 'yusuf', 'yusuf@gmail.com', '$2y$12$OU5t1vsFp2UDJRBnw00gH.0ya1x2GoDkDlPWyCc4qlnUs82J0zXvi', 'yusuf', 'pengurus', 'profile.jpg', NULL, '2025-12-07 15:51:03', '2025-12-07 15:51:36');

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `device_hash` varchar(255) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_devices`
--

INSERT INTO `user_devices` (`id`, `user_id`, `device_hash`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 18, '3afa9e7c-ac25-46ad-ad91-ac92f38c2238', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-11-07 21:21:49', '2026-01-04 12:15:34'),
(2, 20, '3c05ab31-1414-4ac7-8a6a-37309441ac46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 OPR/123.0.0.0', '2025-11-08 00:04:49', '2025-11-16 05:24:52'),
(3, 1, 'ab1c49e4-b7b2-4d1d-a584-78b6a6056558', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-11-08 08:12:06', '2025-12-06 13:01:13'),
(4, 15, 'f32becc7-b4b1-450a-b59e-6ece1c52f1f3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 15:47:10', '2025-12-07 15:47:10'),
(8, 21, '2087d018-33fc-4c8a-8dc9-aec7099abe7e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 15:52:25', '2025-12-07 15:52:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_user_id_foreign` (`user_id`),
  ADD KEY `attendances_jadwal_id_foreign` (`jadwal_id`);

--
-- Indexes for table `catatan_aktivitas`
--
ALTER TABLE `catatan_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catatan_aktivitas_siswa_id_foreign` (`siswa_id`);

--
-- Indexes for table `jadwal_absens`
--
ALTER TABLE `jadwal_absens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jadwal_absens_type_unique` (`type`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_nomor_induk_unique` (`nomor_induk`),
  ADD UNIQUE KEY `siswa_no_kartu_rfid_unique` (`no_kartu_rfid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_devices_device_hash_unique` (`device_hash`),
  ADD KEY `user_devices_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `catatan_aktivitas`
--
ALTER TABLE `catatan_aktivitas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jadwal_absens`
--
ALTER TABLE `jadwal_absens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=937;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_absens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `catatan_aktivitas`
--
ALTER TABLE `catatan_aktivitas`
  ADD CONSTRAINT `catatan_aktivitas_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD CONSTRAINT `user_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
