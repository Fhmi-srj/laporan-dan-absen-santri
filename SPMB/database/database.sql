-- =============================================
-- SPMB Database Schema - Production Ready
-- Sistem Penerimaan Murid Baru
-- Version: 1.1 (Compatible dengan MySQL 5.x)
-- =============================================
-- CATATAN: Import file ini ke phpMyAdmin setelah database dibuat
-- Pastikan database sudah dibuat terlebih dahulu di hosting panel

-- ---------------------------------------------
-- Table: admin
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Default admin (password: admin123)
INSERT INTO admin (username, password, nama) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- ---------------------------------------------
-- Table: pendaftaran
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS pendaftaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_registrasi VARCHAR(10),
    -- Data Siswa
    nama VARCHAR(100) NOT NULL,
    lembaga ENUM('SMP NU BP', 'MA ALHIKAM') NOT NULL,
    nisn VARCHAR(20),
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    jumlah_saudara INT DEFAULT 0,
    no_kk VARCHAR(20),
    nik VARCHAR(20),
    provinsi VARCHAR(100),
    kota_kab VARCHAR(100),
    kecamatan VARCHAR(100),
    kelurahan_desa VARCHAR(100),
    alamat TEXT,
    asal_sekolah VARCHAR(100),
    -- Prestasi
    prestasi VARCHAR(200),
    tingkat_prestasi ENUM('KABUPATEN', 'PROVINSI', 'NASIONAL'),
    juara ENUM('1', '2', '3'),
    file_sertifikat VARCHAR(255),
    -- Tambahan
    pip_pkh VARCHAR(50),
    status_mukim ENUM('PONDOK PP MAMBAUL HUDA', 'PONDOK SELAIN PP MAMBAUL HUDA', 'TIDAK PONDOK') NOT NULL,
    sumber_info VARCHAR(50),
    -- Data Ayah
    nama_ayah VARCHAR(100),
    tempat_lahir_ayah VARCHAR(50),
    tanggal_lahir_ayah DATE,
    nik_ayah VARCHAR(20),
    pekerjaan_ayah VARCHAR(100),
    penghasilan_ayah VARCHAR(20),
    -- Data Ibu
    nama_ibu VARCHAR(100),
    tempat_lahir_ibu VARCHAR(50),
    tanggal_lahir_ibu DATE,
    nik_ibu VARCHAR(20),
    pekerjaan_ibu VARCHAR(100),
    penghasilan_ibu VARCHAR(20),
    -- Kontak
    no_hp_wali VARCHAR(20) NOT NULL,
    -- Akun User
    password VARCHAR(255),
    -- Upload Dokumen
    file_kk VARCHAR(255),
    file_ktp_ortu VARCHAR(255),
    file_akta VARCHAR(255),
    file_ijazah VARCHAR(255),
    -- Meta
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Unique phone number (for user account)
    UNIQUE KEY unique_phone (no_hp_wali)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ---------------------------------------------
-- Table: biaya
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS biaya (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kategori ENUM('PENDAFTARAN', 'DAFTAR_ULANG') NOT NULL,
    nama_item VARCHAR(100) NOT NULL,
    biaya_pondok INT DEFAULT 0,
    biaya_smp INT DEFAULT 0,
    biaya_ma INT DEFAULT 0,
    urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default biaya data
INSERT INTO biaya (kategori, nama_item, biaya_pondok, biaya_smp, biaya_ma, urutan) VALUES
('PENDAFTARAN', 'Registrasi', 50000, 20000, 30000, 1),
('DAFTAR_ULANG', 'Baju Batik', 0, 65000, 90000, 1),
('DAFTAR_ULANG', 'Seragam Bawahan', 0, 0, 75000, 2),
('DAFTAR_ULANG', 'Jas Almamater', 170000, 0, 150000, 3),
('DAFTAR_ULANG', 'Kaos Olahraga', 0, 90000, 100000, 4),
('DAFTAR_ULANG', 'Badge Almamater', 0, 35000, 40000, 5),
('DAFTAR_ULANG', 'Buku Raport', 40000, 40000, 35000, 6),
('DAFTAR_ULANG', 'Infaq Bulan Pertama', 600000, 50000, 100000, 7),
('DAFTAR_ULANG', 'Kegiatan & Hari Besar', 150000, 50000, 380000, 8),
('DAFTAR_ULANG', 'Kitab/Buku Pelajaran', 100000, 0, 350000, 9),
('DAFTAR_ULANG', 'Perbaikan Asrama', 500000, 0, 0, 10),
('DAFTAR_ULANG', 'Kartu Santri', 40000, 0, 0, 11),
('DAFTAR_ULANG', 'Kebersihan', 150000, 0, 0, 12),
('DAFTAR_ULANG', 'Kalender', 50000, 0, 0, 13);

-- ---------------------------------------------
-- Table: beasiswa
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS beasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis VARCHAR(100) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    syarat VARCHAR(200) NOT NULL,
    benefit VARCHAR(100) NOT NULL,
    urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default beasiswa data
INSERT INTO beasiswa (jenis, kategori, syarat, benefit, urutan) VALUES
('Tahfidz', 'Penghafal Al-Quran', 'Hafal 1-5 Juz', 'Gratis SPP 1 Bulan', 1),
('Tahfidz', 'Penghafal Al-Quran', 'Hafal 6-10 Juz', 'Gratis SPP 2 Bulan', 2),
('Tahfidz', 'Penghafal Al-Quran', 'Hafal 11-20 Juz', 'Gratis SPP 3 Bulan', 3),
('Tahfidz', 'Penghafal Al-Quran', 'Hafal 21-30 Juz', 'Gratis SPP 6 Bulan', 4),
('Akademik', 'Berdasarkan Nilai Rapor', 'Nilai 90-100', 'Gratis SPP 3 Bulan', 5),
('Akademik', 'Berdasarkan Nilai Rapor', 'Nilai 80-89', 'Gratis SPP 2 Bulan', 6),
('Akademik', 'Berdasarkan Nilai Rapor', 'Nilai 70-79', 'Gratis SPP 1 Bulan', 7),
('Yatim/Piatu', 'Keringanan', 'Yatim/Piatu', 'Potongan 25% SPP', 8),
('Yatim/Piatu', 'Keringanan', 'Yatim Piatu', 'Potongan 50% SPP', 9);

-- ---------------------------------------------
-- Table: kontak
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS kontak (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lembaga VARCHAR(50) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    no_whatsapp VARCHAR(20) NOT NULL,
    link_wa VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default kontak
INSERT INTO kontak (lembaga, nama, no_whatsapp, link_wa) VALUES
('SMP', 'Ust. Rino Mukti', '08123456789', 'http://wa.link/7svsg0'),
('MA', 'Ust. Akrom Adabi', '0856 4164 7478', 'https://wa.link/ire9yv'),
('PONPES', 'Ust. M. Kowi', '08123456790', 'https://wa.link/20sq3q');

-- ---------------------------------------------
-- Table: pengaturan
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS pengaturan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kunci VARCHAR(50) UNIQUE NOT NULL,
    nilai TEXT,
    keterangan VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default settings
INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES
('status_pendaftaran', '1', 'Status pendaftaran: 1=Buka, 0=Tutup'),
('tahun_ajaran', '2026/2027', 'Tahun ajaran aktif'),
('link_pdf_biaya', 'https://s.id/biayapendaftaran', 'Link download PDF biaya'),
('link_pdf_brosur', 'https://drive.google.com/file/d/1G9t4FnrnYo8amlPT9Y2OdLL-NQHDUIMB/view?usp=sharing', 'Link download PDF brosur'),
('link_pdf_syarat', 'https://drive.google.com/file/d/1vbwof-2w_v2wzvosNYTzyE74EJqR0cEQ/view?usp=sharing', 'Link download PDF syarat'),
('link_beasiswa', 'https://s.id/BeasiswaMAHAKAM', 'Link info beasiswa lengkap'),
('gelombang_1_start', '2025-01-01', 'Tanggal mulai gelombang 1'),
('gelombang_1_end', '2025-03-31', 'Tanggal selesai gelombang 1'),
('gelombang_2_start', '2025-04-01', 'Tanggal mulai gelombang 2'),
('gelombang_2_end', '2025-06-30', 'Tanggal selesai gelombang 2'),
('link_grup_wa', '', 'Link grup WhatsApp untuk pendaftar baru');

-- ---------------------------------------------
-- Table: activity_log
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- =============================================
-- SELESAI - Database siap digunakan!
-- =============================================
-- Login Admin: 
--   Username: admin
--   Password: admin123
-- =============================================
