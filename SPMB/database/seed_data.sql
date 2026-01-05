-- =============================================
-- SPMB Seed Data - Complete Sample Data
-- Sistem Penerimaan Murid Baru
-- Version: 1.0
-- Date: 2024-12-24
-- =============================================
-- CATATAN: Import file ini SETELAH database.sql
-- File ini berisi sample data untuk testing
-- =============================================

-- =============================================
-- SAMPLE PENDAFTARAN DATA
-- =============================================
-- Password for all: password123 (hashed)
INSERT INTO pendaftaran (
    no_registrasi, nama, lembaga, nisn, tempat_lahir, tanggal_lahir, jenis_kelamin,
    jumlah_saudara, no_kk, nik, provinsi, kota_kab, kecamatan, kelurahan_desa, alamat,
    asal_sekolah, status_mukim, pip_pkh, sumber_info,
    nama_ayah, tempat_lahir_ayah, tanggal_lahir_ayah, nik_ayah, pekerjaan_ayah, penghasilan_ayah,
    nama_ibu, tempat_lahir_ibu, tanggal_lahir_ibu, nik_ibu, pekerjaan_ibu, penghasilan_ibu,
    no_hp_wali, password, status, created_at
) VALUES
-- Pendaftar 1: SMP, Laki-laki, Verified
('REG001', 'Ahmad Fauzi Rahman', 'SMP NU BP', '1234567890', 'Pekalongan', '2012-05-15', 'L',
 2, '3326010101120001', '3326011505120001', 'Jawa Tengah', 'Pekalongan', 'Kedungwuni', 'Pajomblangan',
 'Jl. Raya Pajomblangan No. 45 RT 02/RW 03',
 'SDN 01 Kedungwuni', 'PONDOK PP MAMBAUL HUDA', 'Tidak', 'Instagram',
 'H. Abdul Rahman', 'Pekalongan', '1975-03-20', '3326012003750001', 'Pedagang', '3-5 Juta',
 'Hj. Siti Fatimah', 'Pekalongan', '1978-08-10', '3326011008780001', 'IRT', 'Tidak Ada',
 '081234567001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', '2025-01-20 08:30:00'),

-- Pendaftar 2: MA, Perempuan, Verified
('REG002', 'Aisyah Putri Anggraini', 'MA ALHIKAM', '1234567891', 'Batang', '2009-08-22', 'P',
 3, '3326010101120002', '3326012208090001', 'Jawa Tengah', 'Batang', 'Batang', 'Kauman',
 'Jl. KH. Ahmad Dahlan No. 12 RT 01/RW 02',
 'MTs Negeri 1 Batang', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Penerima PKH', 'Teman/Saudara',
 'Drs. Ahmad Syafii', 'Batang', '1972-11-05', '3326010511720001', 'Guru', '3-5 Juta',
 'Nur Hidayah S.Pd', 'Batang', '1975-04-18', '3326011804750001', 'Guru', '3-5 Juta',
 '081234567002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', '2025-01-22 10:15:00'),

-- Pendaftar 3: SMP, Laki-laki, Pending
('REG003', 'Muhammad Rizki Pratama', 'SMP NU BP', '1234567892', 'Pemalang', '2012-02-28', 'L',
 1, '3326010101120003', '3326012802120001', 'Jawa Tengah', 'Pemalang', 'Pemalang', 'Mulyoharjo',
 'Ds. Mulyoharjo RT 04/RW 01',
 'SDN 02 Pemalang', 'TIDAK PONDOK', 'Tidak', 'Brosur',
 'Bambang Susanto', 'Pemalang', '1980-06-14', '3326011406800001', 'Wiraswasta', '5-10 Juta',
 'Emi Sulistyowati', 'Pemalang', '1982-09-25', '3326012509820001', 'IRT', 'Tidak Ada',
 '081234567003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending', '2025-02-05 14:20:00'),

-- Pendaftar 4: MA, Perempuan, Pending
('REG004', 'Farah Naila Zahra', 'MA ALHIKAM', '1234567893', 'Pekalongan', '2009-12-03', 'P',
 2, '3326010101120004', '3326010312090001', 'Jawa Tengah', 'Pekalongan', 'Buaran', 'Simbang Kulon',
 'Jl. Simbang Raya No. 78 RT 03/RW 04',
 'MTs Salafiyah Pekalongan', 'PONDOK PP MAMBAUL HUDA', 'Tidak', 'Website',
 'H. Khoirul Anam', 'Pekalongan', '1970-01-30', '3326013001700001', 'Pengusaha', '>10 Juta',
 'Hj. Aminah', 'Pekalongan', '1973-07-12', '3326011207730001', 'IRT', 'Tidak Ada',
 '081234567004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending', '2025-02-08 09:45:00'),

-- Pendaftar 5: SMP, Perempuan, Verified
('REG005', 'Nabila Azzahra Putri', 'SMP NU BP', '1234567894', 'Pekalongan', '2012-07-19', 'P',
 4, '3326010101120005', '3326011907120001', 'Jawa Tengah', 'Pekalongan', 'Tirto', 'Tirto',
 'Perum Tirto Indah Blok C-15 RT 05/RW 02',
 'SD Islam Al-Irsyad', 'PONDOK PP MAMBAUL HUDA', 'Tidak', 'Instagram',
 'Ir. Sutrisno M.T.', 'Semarang', '1976-04-08', '3326010804760001', 'PNS', '5-10 Juta',
 'Dr. Ratna Dewi', 'Semarang', '1978-10-22', '3326012210780001', 'Dokter', '>10 Juta',
 '081234567005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', '2025-02-10 11:30:00'),

-- Pendaftar 6: MA, Laki-laki, Rejected
('REG006', 'Dimas Arya Pratama', 'MA ALHIKAM', '1234567895', 'Batang', '2009-03-25', 'L',
 2, '3326010101120006', '3326012503090001', 'Jawa Tengah', 'Batang', 'Gringsing', 'Gringsing',
 'Ds. Gringsing RT 02/RW 01',
 'MTs Darul Ulum', 'TIDAK PONDOK', 'Penerima PIP', 'Teman/Saudara',
 'Suparman', 'Batang', '1978-12-01', '3326010112780001', 'Petani', '<1 Juta',
 'Sumarni', 'Batang', '1980-05-17', '3326011705800001', 'Buruh', '<1 Juta',
 '081234567006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rejected', '2025-02-12 16:00:00'),

-- Pendaftar 7: SMP, Laki-laki, Pending
('REG007', 'Rafi Akbar Maulana', 'SMP NU BP', '1234567896', 'Pekalongan', '2012-09-10', 'L',
 1, '3326010101120007', '3326011009120001', 'Jawa Tengah', 'Pekalongan', 'Wiradesa', 'Pekuncen',
 'Jl. Pekuncen Raya No. 23 RT 01/RW 03',
 'SD Muhammadiyah Wiradesa', 'PONDOK PP MAMBAUL HUDA', 'Tidak', 'Facebook',
 'Hendra Wijaya', 'Pekalongan', '1979-02-14', '3326011402790001', 'Swasta', '3-5 Juta',
 'Linda Permata', 'Pekalongan', '1981-06-30', '3326013006810001', 'Swasta', '3-5 Juta',
 '081234567007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending', '2025-02-15 08:00:00'),

-- Pendaftar 8: MA, Perempuan, Verified
('REG008', 'Siti Nur Haliza', 'MA ALHIKAM', '1234567897', 'Pekalongan', '2009-11-28', 'P',
 5, '3326010101120008', '3326012811090001', 'Jawa Tengah', 'Pekalongan', 'Kedungwuni', 'Langkap',
 'Ds. Langkap RT 06/RW 02',
 'MTs Maarif Kedungwuni', 'PONDOK PP MAMBAUL HUDA', 'Penerima PKH', 'Spanduk/Banner',
 'KH. Muhammad Ilyas', 'Pekalongan', '1965-08-17', '3326011708650001', 'Ulama/Kyai', '1-3 Juta',
 'Nyai Hj. Maryam', 'Pekalongan', '1970-03-05', '3326010503700001', 'IRT', 'Tidak Ada',
 '081234567008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', '2025-02-18 13:45:00'),

-- Pendaftar 9: SMP, Laki-laki, Pending  
('REG009', 'Fahri Ramadhan Putra', 'SMP NU BP', '1234567898', 'Pemalang', '2012-04-02', 'L',
 3, '3326010101120009', '3326010204120001', 'Jawa Tengah', 'Pemalang', 'Comal', 'Comal',
 'Jl. Comal Indah No. 56 RT 04/RW 05',
 'SDN 03 Comal', 'PONDOK SELAIN PP MAMBAUL HUDA', 'Tidak', 'YouTube',
 'Agus Salim', 'Pemalang', '1977-07-21', '3326012107770001', 'TNI', '5-10 Juta',
 'Dewi Sartika', 'Pemalang', '1980-01-10', '3326011001800001', 'IRT', 'Tidak Ada',
 '081234567009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending', '2025-02-20 10:30:00'),

-- Pendaftar 10: MA, Laki-laki, Verified
('REG010', 'Muhammad Hafiz Anwar', 'MA ALHIKAM', '1234567899', 'Pekalongan', '2009-06-14', 'L',
 2, '3326010101120010', '3326011406090001', 'Jawa Tengah', 'Pekalongan', 'Kajen', 'Kajen',
 'Jl. Kajen Raya No. 100 RT 02/RW 04',
 'MTs Negeri 2 Pekalongan', 'PONDOK PP MAMBAUL HUDA', 'Tidak', 'Website',
 'Drs. H. Anwar Sanusi', 'Pekalongan', '1968-09-08', '3326010809680001', 'Dosen', '5-10 Juta',
 'Hj. Nurul Hidayah', 'Pekalongan', '1972-12-25', '3326012512720001', 'Dosen', '5-10 Juta',
 '081234567010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', '2025-02-22 15:15:00');

-- =============================================
-- SAMPLE ACTIVITY LOG
-- =============================================
INSERT INTO activity_log (admin_id, action, description, ip_address, created_at) VALUES
(1, 'LOGIN', 'Admin login ke sistem', '127.0.0.1', '2025-01-20 07:00:00'),
(1, 'STATUS_UPDATE', 'Verifikasi pendaftaran: Ahmad Fauzi Rahman', '127.0.0.1', '2025-01-20 08:35:00'),
(1, 'STATUS_UPDATE', 'Verifikasi pendaftaran: Aisyah Putri Anggraini', '127.0.0.1', '2025-01-22 10:20:00'),
(1, 'LOGIN', 'Admin login ke sistem', '127.0.0.1', '2025-02-10 08:00:00'),
(1, 'STATUS_UPDATE', 'Verifikasi pendaftaran: Nabila Azzahra Putri', '127.0.0.1', '2025-02-10 11:35:00'),
(1, 'STATUS_UPDATE', 'Tolak pendaftaran: Dimas Arya Pratama (dokumen tidak lengkap)', '127.0.0.1', '2025-02-12 16:05:00'),
(1, 'STATUS_UPDATE', 'Verifikasi pendaftaran: Siti Nur Haliza', '127.0.0.1', '2025-02-18 13:50:00'),
(1, 'STATUS_UPDATE', 'Verifikasi pendaftaran: Muhammad Hafiz Anwar', '127.0.0.1', '2025-02-22 15:20:00'),
(1, 'UPDATE', 'Update data pengaturan tahun ajaran', '127.0.0.1', '2025-02-25 09:00:00');

-- =============================================
-- SELESAI - Seed Data Imported!
-- =============================================
-- Summary:
--   - 10 Sample pendaftaran (5 verified, 4 pending, 1 rejected)
--   - 9 Activity log entries
-- 
-- Sample User Credentials:
--   Phone: 081234567001 - 081234567010
--   Password: password123 (for all)
-- =============================================
