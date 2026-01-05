-- =============================================
-- Tambahkan kolom alamat terpisah ke tabel pendaftaran
-- Jalankan query ini di phpMyAdmin atau MySQL client
-- =============================================

-- Tambah kolom alamat terpisah
ALTER TABLE pendaftaran 
ADD COLUMN provinsi VARCHAR(100) AFTER alamat,
ADD COLUMN kota_kab VARCHAR(100) AFTER provinsi,
ADD COLUMN kecamatan VARCHAR(100) AFTER kota_kab,
ADD COLUMN kelurahan_desa VARCHAR(100) AFTER kecamatan;

-- Catatan: kolom 'alamat' yang sudah ada akan digunakan untuk 'Detail Alamat'
