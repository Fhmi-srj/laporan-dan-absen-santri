-- =============================================
-- Tambahkan kolom no_registrasi ke tabel pendaftaran
-- Jalankan query ini di phpMyAdmin atau MySQL client
-- =============================================

-- Tambah kolom no_registrasi jika belum ada
ALTER TABLE pendaftaran ADD COLUMN no_registrasi VARCHAR(10) AFTER id;

-- Update pendaftar yang sudah ada dengan nomor registrasi berurutan
SET @row_number = 0;
UPDATE pendaftaran 
SET no_registrasi = LPAD((@row_number := @row_number + 1), 3, '0')
ORDER BY id ASC;

-- Tambah setting link_grup_wa jika belum ada
INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES 
('link_grup_wa', '', 'Link grup WhatsApp untuk pendaftar baru')
ON DUPLICATE KEY UPDATE keterangan = 'Link grup WhatsApp untuk pendaftar baru';
