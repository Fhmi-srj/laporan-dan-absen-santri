-- Tabel untuk menyimpan history surat izin yang sudah dicetak
CREATE TABLE IF NOT EXISTS print_izin_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_surat VARCHAR(50) NOT NULL,
    kategori ENUM('sakit','izin_pulang') NOT NULL,
    santri_ids JSON NOT NULL,
    santri_names TEXT,
    tujuan_guru VARCHAR(100),
    kelas VARCHAR(50),
    tanggal DATE NOT NULL,
    printed_by BIGINT UNSIGNED,
    printed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (printed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_nomor_surat (nomor_surat),
    INDEX idx_printed_at (printed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
