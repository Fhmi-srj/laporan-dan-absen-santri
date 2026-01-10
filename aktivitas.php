<?php
/**
 * Aktivitas Siswa - Halaman Utama
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$csrfToken = generateCsrfToken();
$flash = getFlash();
$pageTitle = 'Monitoring Aktivitas';

// Extra CSS untuk DataTables
$extraCss = '<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">';

// Extra styles khusus halaman ini
$extraStyles = <<<'CSS'
<style>
    .fw-medium { font-weight: 500; }
    .fw-semibold { font-weight: 600; }
    .text-primary-custom { color: var(--primary-color); }
    
    /* DataTables Sorting - Override default icons completely */
    table.dataTable thead th.sorting,
    table.dataTable thead th.sorting_asc,
    table.dataTable thead th.sorting_desc {
        background-image: none !important;
        padding-right: 28px !important;
        cursor: pointer;
    }
    table.dataTable thead th.sorting:after,
    table.dataTable thead th.sorting_asc:after,
    table.dataTable thead th.sorting_desc:after {
        content: "\f0dc" !important;
        font-family: "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        position: absolute !important;
        right: 10px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        opacity: 0.3 !important;
        font-size: 0.7rem !important;
        bottom: auto !important;
    }
    table.dataTable thead th.sorting_asc:after {
        content: "\f0de" !important;
        opacity: 1 !important;
        color: var(--primary-color) !important;
    }
    table.dataTable thead th.sorting_desc:after {
        content: "\f0dd" !important;
        opacity: 1 !important;
        color: var(--primary-color) !important;
    }
    table.dataTable thead th:hover {
        background-color: #f1f5f9 !important;
    }
    
    .section-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
    }
    
    .search-wrapper { position: relative; }
    
    .search-input {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 12px 45px 12px 15px;
        height: 48px;
        font-size: 0.95rem;
        width: 100%;
        transition: all 0.2s;
    }
    
    .search-input:focus {
        background: #fff;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(134, 89, 241, 0.1);
        outline: none;
    }
    
    .btn-qr-scan {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        height: 34px; width: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        background-color: #fff;
        color: var(--primary-color);
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .btn-qr-scan:hover { background-color: var(--primary-color); color: #fff; }
    
    .student-info-empty {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background-color: #f8fafc;
    }
    
    .student-card-active {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        border-radius: 16px;
        color: white;
        padding: 25px;
        position: relative;
        box-shadow: 0 10px 20px -5px rgba(134, 89, 241, 0.4);
    }
    
    .student-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.9rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        padding-bottom: 6px;
    }
    .student-info-row:last-child { border-bottom: none; margin-bottom: 0; }
    .student-label { opacity: 0.85; font-weight: 400; font-size: 0.85rem; }
    .student-value { font-weight: 600; }
    
    .cat-btn {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        transition: all 0.2s;
        height: 100%;
        display: flex; align-items: center;
        width: 100%;
        text-align: left;
        color: #334155;
        position: relative;
        overflow: visible;
    }
    .cat-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: #cbd5e1; }
    
    .cat-icon-box {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-right: 10px;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    
    .theme-sakit { color: #ef4444; background: #fef2f2; }
    .theme-izin-keluar { color: #f59e0b; background: #fffbeb; }
    .theme-izin-pulang { color: #f97316; background: #fff7ed; }
    .theme-sambangan { color: #10b981; background: #ecfdf5; }
    .theme-pelanggaran { color: #db2777; background: #fdf2f8; }
    .theme-paket { color: #3b82f6; background: #eff6ff; }
    .theme-hafalan { color: #3b82f6; background: #dbeafe; }
    .theme-izin-sekolah { color: #059669; background: #d1fae5; }
    
    #table-aktivitas { width: 100% !important; }
    #table-aktivitas thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 16px;
        white-space: nowrap;
    }
    #table-aktivitas tbody td {
        padding: 12px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
    }
    
    .custom-select-filter {
        background-color: #f1f5f9;
        border: 1px solid transparent;
        font-weight: 600;
        color: #475569;
        padding: 0.5rem 2rem 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
    }
    .custom-select-filter:focus { background-color: white; border-color: var(--primary-color); box-shadow: 0 0 0 2px rgba(134, 89, 241, 0.1); }
    
    .modal-content { border: none; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 1.5rem; }
    .modal-body { padding: 1.5rem; max-height: 60vh; overflow-y: auto; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 1.25rem 1.5rem; }
    .modal-dialog-scrollable .modal-body { max-height: calc(100vh - 200px); }
    
    .form-label-custom {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.025em;
        margin-bottom: 0.5rem;
    }
    
    .form-control-custom {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    .form-control-custom:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(134, 89, 241, 0.1); outline: none; }
    
    /* Photo Upload Component */
    .photo-upload-wrapper {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s;
    }
    .photo-upload-wrapper:hover {
        border-color: var(--primary-color);
        background: #f1f5f9;
    }
    .photo-upload-wrapper.has-preview {
        border-style: solid;
        border-color: #10b981;
        background: #ecfdf5;
    }
    .photo-upload-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-photo-upload {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    .btn-camera {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    .btn-camera:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    .btn-file {
        background: white;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .btn-file:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .photo-preview-container {
        position: relative;
        display: inline-block;
        margin-top: 15px;
    }
    .photo-preview-container img {
        max-width: 100%;
        max-height: 180px;
        border-radius: 10px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .btn-remove-photo {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #ef4444;
        color: white;
        border: 2px solid white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .btn-remove-photo:hover {
        background: #dc2626;
        transform: scale(1.1);
    }
    @media (max-width: 768px) {
        .photo-upload-buttons {
            flex-direction: column;
        }
        .btn-photo-upload {
            width: 100%;
            justify-content: center;
        }
    }
    
    .foto-preview { max-width: 100%; max-height: 150px; border-radius: 8px; margin-top: 10px; border: 1px solid #e2e8f0; }
    
    .autocomplete-list {
        position: absolute;
        z-index: 1050;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        background: white;
    }
    
    @media (max-width: 767.98px) {
        .card-header-custom { flex-direction: column; align-items: flex-start !important; padding: 1rem; }
        .header-tools-wrapper { width: 100%; display: flex; flex-direction: column; gap: 0.75rem; }
    }
    
    /* Print Izin Checkbox Styling */
    .print-santri-check {
        width: 20px !important;
        height: 20px !important;
        margin-right: 12px !important;
        margin-top: 0 !important;
        cursor: pointer;
        accent-color: #059669;
        flex-shrink: 0;
    }
    .print-santri-check:checked {
        background-color: #059669;
        border-color: #059669;
    }
    #print_santri_list .form-check {
        display: flex;
        align-items: flex-start;
        padding: 12px;
        margin: 0;
        border-bottom: 1px solid #e2e8f0;
        transition: background-color 0.2s;
    }
    #print_santri_list .form-check:last-child {
        border-bottom: none;
    }
    #print_santri_list .form-check:hover {
        background-color: #f0fdf4 !important;
    }
    #print_santri_list .form-check-label {
        cursor: pointer;
        flex: 1;
    }
</style>
CSS;
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<div class="main-content">
    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show border-0 shadow-sm mb-4"
            role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check' : 'exclamation' ?>-circle fs-4 me-3"></i>
                <div><?= e($flash['message']) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4"><i class="fas fa-clipboard-list me-2"></i>Monitoring Aktivitas</h4>

    <div class="row g-4">
        <!-- KOLOM KIRI: INPUT DATA -->
        <div class="col-lg-4">
            <div class="card-custom h-100">
                <div class="card-body p-4">
                    <!-- PENCARIAN -->
                    <div class="mb-4 position-relative">
                        <div class="section-title">Pilih Siswa</div>
                        <div class="search-wrapper">
                            <input type="text" id="input_cari" class="search-input" placeholder="Cari nama atau NIS..."
                                autocomplete="off">
                            <button class="btn-qr-scan" id="btn_buka_kamera" title="Scan QR"><i
                                    class="fas fa-qrcode"></i></button>
                        </div>
                        <div id="hasil_autocomplete" class="list-group autocomplete-list d-none"></div>
                        <div id="area_kamera" class="mt-3 d-none rounded-3 overflow-hidden shadow-sm">
                            <div id="reader" style="width: 100%;"></div>
                            <button type="button" id="btn_tutup_kamera"
                                class="btn btn-danger btn-sm w-100 rounded-0 mt-1">
                                <i class="fas fa-times me-1"></i> Tutup Kamera
                            </button>
                        </div>
                    </div>

                    <!-- CARD SISWA -->
                    <div id="card_siswa_wrapper" class="mb-4">
                        <div id="empty_card" class="student-info-empty">
                            <i class="far fa-id-card fa-3x mb-3" style="color: #cbd5e1;"></i>
                            <p class="mb-0 small text-muted fw-medium">Belum ada siswa dipilih</p>
                        </div>
                        <div id="card_siswa" class="student-card-active d-none">
                            <button type="button" id="btn_reset"
                                class="btn-close btn-close-white position-absolute top-0 end-0 m-3 opacity-75"></button>
                            <h4 id="lbl_nama" class="fw-bold mb-3 text-truncate pe-4">Nama Siswa</h4>
                            <div class="student-info-row"><span class="student-label">NIS</span><span
                                    class="student-value" id="lbl_nis">-</span></div>
                            <div class="student-info-row"><span class="student-label">Kelas</span><span
                                    class="student-value" id="lbl_kelas">-</span></div>
                            <div class="student-info-row"><span class="student-label">Alamat</span><span
                                    class="student-value text-truncate" style="max-width: 150px;"
                                    id="lbl_alamat">-</span></div>
                            <input type="hidden" id="selected_siswa_id">
                            <input type="hidden" id="selected_siswa_phone">
                        </div>
                    </div>

                    <!-- GRID TOMBOL KATEGORI -->
                    <div id="panel_menu">
                        <div class="section-title">Input Data</div>
                        <div class="row g-3">
                            <div class="col-6">
                                <button onclick="handleCategoryClick('sakit')" class="cat-btn">
                                    <div class="cat-icon-box theme-sakit"><i class="fas fa-procedures"></i></div>
                                    <span class="fw-bold small">Sakit</span>
                                </button>
                            </div>
                            <?php if ($role !== 'kesehatan'): ?>
                                <div class="col-6">
                                    <button onclick="handleCategoryClick('izin_keluar')" class="cat-btn">
                                        <div class="cat-icon-box theme-izin-keluar"><i class="fas fa-sign-out-alt"></i>
                                        </div>
                                        <span class="fw-bold small">Izin Keluar</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button onclick="handleCategoryClick('izin_pulang')" class="cat-btn">
                                        <div class="cat-icon-box theme-izin-pulang"><i class="fas fa-home"></i></div>
                                        <span class="fw-bold small">Izin Pulang</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button onclick="handleCategoryClick('sambangan')" class="cat-btn">
                                        <div class="cat-icon-box theme-sambangan"><i class="fas fa-users"></i></div>
                                        <span class="fw-bold small">Sambangan</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button onclick="handleCategoryClick('pelanggaran')" class="cat-btn">
                                        <div class="cat-icon-box theme-pelanggaran"><i
                                                class="fas fa-exclamation-triangle"></i></div>
                                        <span class="fw-bold small">Pelanggaran</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button onclick="handleCategoryClick('paket')" class="cat-btn">
                                        <div class="cat-icon-box theme-paket"><i class="fas fa-box-open"></i></div>
                                        <span class="fw-bold small">Paket</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <?php if ($role === 'admin' || $role === 'guru'): ?>
                                <div class="col-6">
                                    <button onclick="handleCategoryClick('hafalan')" class="cat-btn">
                                        <div class="cat-icon-box theme-hafalan"><i class="fas fa-quran"></i></div>
                                        <span class="fw-bold small">Hafalan</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button onclick="openPrintIzinModal()" class="cat-btn">
                                        <div class="cat-icon-box theme-izin-sekolah"><i class="fas fa-print"></i></div>
                                        <span class="fw-bold small">Izin Sekolah</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: TABEL RIWAYAT -->
        <div class="col-lg-8">
            <div class="card-custom h-100">
                <div class="card-header-custom">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100">
                        <!-- Title + Category Filter -->
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center flex-shrink-0">
                                <i class="fas fa-history me-2 text-primary-custom"></i>
                                <span class="d-none d-lg-inline">RIWAYAT AKTIVITAS</span>
                                <span class="d-lg-none">RIWAYAT</span>
                            </h6>
                            <select id="filter_kategori" class="custom-select-filter" style="min-width: 150px;">
                                <option value="all">SEMUA KATEGORI</option>
                                <option value="sakit">SAKIT</option>
                                <?php if ($role !== 'kesehatan'): ?>
                                    <option value="izin_keluar">IZIN KELUAR</option>
                                    <option value="izin_pulang">IZIN PULANG</option>
                                    <option value="sambangan">SAMBANGAN</option>
                                    <option value="paket">PAKET</option>
                                    <option value="pelanggaran">PELANGGARAN</option>
                                <?php endif; ?>
                                <?php if ($role === 'admin' || $role === 'guru'): ?>
                                    <option value="hafalan">HAFALAN</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <!-- Date Filters + Search -->
                        <div class="d-flex align-items-center gap-2 flex-nowrap">
                            <input type="date" id="filter_tanggal_dari" class="form-control form-control-sm"
                                style="width: 130px;">
                            <span class="text-muted">-</span>
                            <input type="date" id="filter_tanggal_sampai" class="form-control form-control-sm"
                                style="width: 130px;">
                            <input type="text" id="filter_search" class="form-control form-control-sm"
                                placeholder="Cari..." style="width: 100px;">
                            <button class="btn btn-light border btn-sm px-3" onclick="refreshTable()"
                                id="btn-refresh"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div id="bulk-actions"
                    class="bg-success bg-opacity-10 px-4 py-2 d-none d-flex justify-content-between align-items-center border-bottom border-success border-opacity-25">
                    <span class="small fw-bold text-success"><i class="fas fa-check-circle me-1"></i> <span
                            id="selected-count">0</span> data terpilih</span>
                    <div>
                        <button id="btn-bulk-wa" class="btn btn-success btn-sm border-0 fw-bold shadow-sm"><i
                                class="fab fa-whatsapp me-1"></i> WA Massal</button>
                        <button id="btn-bulk-report" class="btn btn-primary btn-sm border-0 ms-2 fw-bold shadow-sm"
                            disabled title="Pilih item dengan kategori yang sama"><i class="fas fa-file-alt me-1"></i>
                            Laporan</button>
                        <?php if ($role === 'admin'): ?>
                            <button id="btn-bulk-delete" class="btn btn-danger btn-sm border-0 ms-2 fw-bold shadow-sm"><i
                                    class="fas fa-trash-alt me-1"></i> Hapus</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover w-100 mb-0" id="table-aktivitas">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL INPUT DATA -->
<div class="modal fade" id="modalInput" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formAktivitas" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="log_id" id="modal_log_id">
                <input type="hidden" name="siswa_id" id="modal_siswa_id">
                <input type="hidden" name="kategori" id="modal_kategori">

                <div class="modal-header text-white border-0" style="background-color: var(--primary-color);">
                    <h6 class="modal-title fw-bold" id="modalTitle">INPUT DATA</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light bg-opacity-50">
                    <div class="row g-3">
                        <div class="col-md-4" id="col_tanggal_mulai">
                            <label class="form-label-custom" id="lbl_tanggal_mulai">TANGGAL MULAI</label>
                            <input type="datetime-local" name="tanggal" id="input_tanggal"
                                class="form-control-custom w-100" required>
                        </div>
                        <div class="col-md-4 d-none" id="group_batas_waktu">
                            <label class="form-label-custom">BATAS WAKTU <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="batas_waktu" id="input_batas_waktu"
                                class="form-control-custom w-100">
                        </div>
                        <div class="col-md-4 d-none" id="group_tanggal_selesai">
                            <label class="form-label-custom" id="lbl_tanggal_selesai">TANGGAL SELESAI</label>
                            <input type="datetime-local" name="tanggal_selesai" id="input_tanggal_selesai"
                                class="form-control-custom w-100">
                            <small class="text-muted">Opsional</small>
                        </div>
                        <div class="col-12" id="group_judul">
                            <label class="form-label-custom" id="lbl_judul">JUDUL</label>
                            <input type="text" name="judul" id="input_judul" class="form-control-custom w-100"
                                placeholder="...">
                        </div>
                        <div class="col-12 d-none" id="group_sambangan">
                            <label class="form-label-custom">STATUS PENJENGUK</label>
                            <select name="status_sambangan" id="select_status_sambangan"
                                class="form-select form-control-custom w-100">
                                <option value="">-- Pilih --</option>
                                <option value="Keluarga">Keluarga Inti</option>
                                <option value="Kerabat">Kerabat</option>
                                <option value="Teman">Teman</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12 d-none" id="group_status_sakit">
                            <label class="form-label-custom">STATUS PERIKSA</label>
                            <select name="status_kegiatan" id="select_status_kegiatan"
                                class="form-select form-control-custom w-100">
                                <option value="Belum Diperiksa">Belum Diperiksa</option>
                                <option value="Sudah Diperiksa">Sudah Diperiksa</option>
                            </select>
                        </div>
                        <div class="col-12 d-none" id="group_status_paket">
                            <label class="form-label-custom">STATUS PAKET <span class="text-danger">*</span></label>
                            <select name="status_paket" id="select_status_paket"
                                class="form-select form-control-custom w-100">
                                <option value="Belum Diterima">Belum Diterima</option>
                                <option value="Sudah Diterima">Sudah Diterima</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">KETERANGAN</label>
                            <textarea name="keterangan" id="textarea_keterangan" class="form-control-custom w-100"
                                rows="3" placeholder="Tambahkan detail..."></textarea>
                        </div>
                        <div class="col-12" id="group_foto">
                            <div class="row g-3">
                                <div class="col-md-6" id="col_foto_1">
                                    <label class="form-label-custom" id="lbl_foto_1">FOTO BUKTI <span
                                            class="text-muted fw-normal">(Opsional)</span></label>
                                    <div class="photo-upload-wrapper" id="wrapper_foto_1">
                                        <input type="file" name="foto_dokumen_1" id="input_foto_1" class="d-none"
                                            accept="image/*"
                                            onchange="handlePhotoSelect(this, 'preview_foto_1', 'wrapper_foto_1')">
                                        <input type="file" id="camera_foto_1" class="d-none" accept="image/*"
                                            capture="environment"
                                            onchange="handlePhotoSelect(this, 'preview_foto_1', 'wrapper_foto_1', 'input_foto_1')">
                                        <div class="photo-upload-buttons" id="buttons_foto_1">
                                            <button type="button" class="btn-photo-upload btn-camera"
                                                onclick="document.getElementById('camera_foto_1').click()">
                                                <i class="fas fa-camera"></i> Ambil Foto
                                            </button>
                                            <button type="button" class="btn-photo-upload btn-file"
                                                onclick="document.getElementById('input_foto_1').click()">
                                                <i class="fas fa-folder-open"></i> Pilih File
                                            </button>
                                        </div>
                                        <div class="photo-preview-container d-none" id="container_foto_1">
                                            <img id="preview_foto_1" alt="Preview">
                                            <button type="button" class="btn-remove-photo"
                                                onclick="removePhoto('input_foto_1', 'preview_foto_1', 'wrapper_foto_1', 'container_foto_1', 'buttons_foto_1')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 d-none" id="col_foto_2">
                                    <label class="form-label-custom" id="lbl_foto_2">FOTO PENERIMA</label>
                                    <div class="photo-upload-wrapper" id="wrapper_foto_2">
                                        <input type="file" name="foto_dokumen_2" id="input_foto_2" class="d-none"
                                            accept="image/*"
                                            onchange="handlePhotoSelect(this, 'preview_foto_2', 'wrapper_foto_2')">
                                        <input type="file" id="camera_foto_2" class="d-none" accept="image/*"
                                            capture="environment"
                                            onchange="handlePhotoSelect(this, 'preview_foto_2', 'wrapper_foto_2', 'input_foto_2')">
                                        <div class="photo-upload-buttons" id="buttons_foto_2">
                                            <button type="button" class="btn-photo-upload btn-camera"
                                                onclick="document.getElementById('camera_foto_2').click()">
                                                <i class="fas fa-camera"></i> Ambil Foto
                                            </button>
                                            <button type="button" class="btn-photo-upload btn-file"
                                                onclick="document.getElementById('input_foto_2').click()">
                                                <i class="fas fa-folder-open"></i> Pilih File
                                            </button>
                                        </div>
                                        <div class="photo-preview-container d-none" id="container_foto_2">
                                            <img id="preview_foto_2" alt="Preview">
                                            <button type="button" class="btn-remove-photo"
                                                onclick="removePhoto('input_foto_2', 'preview_foto_2', 'wrapper_foto_2', 'container_foto_2', 'buttons_foto_2')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL WA MASSAL PERSONAL -->
<div class="modal fade" id="modalBulkWa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header text-white border-0" style="background-color: #25D366;">
                <h6 class="modal-title fw-bold"><i class="fab fa-whatsapp me-2"></i>WA MASSAL PERSONAL</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div
                    class="alert alert-success bg-success bg-opacity-10 border-0 p-3 mb-3 small text-success rounded-3">
                    <i class="fas fa-info-circle me-1"></i> Kirim pemberitahuan personal ke <strong><span
                            id="bulk_wa_count">0</span> wali</strong>
                    <br><small class="text-muted">Setiap wali menerima detail aktivitas anaknya masing-masing</small>
                </div>

                <!-- List Penerima -->
                <div class="mb-3">
                    <label class="form-label-custom mb-2">DAFTAR PENERIMA</label>
                    <div id="bulk_wa_list" class="border rounded-3 p-2"
                        style="max-height: 180px; overflow-y: auto; background: #f8fafc;">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <!-- Preview Format -->
                <div class="mb-3">
                    <label class="form-label-custom mb-2">CONTOH FORMAT PESAN</label>
                    <div id="bulk_wa_preview" class="border rounded-3 p-3 small"
                        style="background: #f0fdf4; font-family: monospace; white-space: pre-wrap;">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4 fw-bold shadow-sm" id="btn_send_bulk_wa"><i
                        class="fab fa-whatsapp me-1"></i> KIRIM SEKARANG</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KIRIM WA SINGLE -->
<div class="modal fade" id="modalSendWa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header text-white border-0" style="background-color: #10b981;">
                <h6 class="modal-title fw-bold"><i class="fab fa-whatsapp me-2"></i>KIRIM WA KE WALI</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label-custom">ISI PESAN</label>
                    <textarea id="single_wa_message" class="form-control-custom w-100" rows="6"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4 fw-bold shadow-sm" id="btn_send_single_wa"><i
                        class="fas fa-paper-plane me-1"></i> KIRIM SEKARANG</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REPORT PREVIEW -->
<div class="modal fade" id="modalReport" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header text-white border-0" style="background-color: #3b82f6;">
                <h6 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>PREVIEW LAPORAN</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info bg-info bg-opacity-10 border-0 p-3 mb-3 small text-info rounded-3">
                    <i class="fas fa-info-circle me-1"></i> Laporan dari <strong><span id="report_count">0</span>
                        data</strong> dengan kategori <strong><span id="report_category">-</span></strong>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">TEKS LAPORAN</label>
                    <textarea id="report_text" class="form-control-custom w-100" rows="12" readonly
                        style="font-family: monospace; font-size: 0.85rem; white-space: pre-wrap;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" id="btn_copy_report"><i
                        class="fas fa-copy me-1"></i> COPY TEKS</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PRINT IZIN SEKOLAH -->
<div class="modal fade" id="modalPrintIzin" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header text-white border-0"
                style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <h6 class="modal-title fw-bold"><i class="fas fa-print me-2"></i>CETAK SURAT IZIN SEKOLAH</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Left: Santri Selection -->
                    <div class="col-lg-7">
                        <div class="mb-3">
                            <label class="form-label-custom">PILIH KATEGORI</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="print_kategori" id="print_kat_sakit"
                                    value="sakit" checked>
                                <label class="btn btn-outline-danger" for="print_kat_sakit"><i
                                        class="fas fa-procedures me-1"></i> Sakit</label>
                                <input type="radio" class="btn-check" name="print_kategori" id="print_kat_izin"
                                    value="izin_pulang">
                                <label class="btn btn-outline-warning" for="print_kat_izin"><i
                                        class="fas fa-home me-1"></i> Izin Pulang</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">PILIH SANTRI <span class="text-muted fw-normal">(Max
                                    5)</span></label>
                            <div id="print_santri_list" class="border rounded-3 p-2"
                                style="max-height: 300px; overflow-y: auto; background: #f8fafc;">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Memuat data...
                                </div>
                            </div>
                            <small class="text-muted"><span id="print_selected_count">0</span>/5 santri dipilih</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">TUJUAN GURU <span class="text-danger">*</span></label>
                                <input type="text" id="print_tujuan_guru" class="form-control form-control-custom"
                                    placeholder="Contoh: Wali Kelas 7A">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">KELAS <span class="text-danger">*</span></label>
                                <input type="text" id="print_kelas" class="form-control form-control-custom"
                                    placeholder="Contoh: 7A, 7B, 8C">
                            </div>
                        </div>
                    </div>

                    <!-- Right: Preview -->
                    <div class="col-lg-5">
                        <label class="form-label-custom">PREVIEW SURAT</label>
                        <div id="print_preview" class="border rounded-3 p-3"
                            style="background: #fffef7; font-family: 'Courier New', monospace; font-size: 11px; white-space: pre-wrap; min-height: 400px; max-height: 450px; overflow-y: auto; line-height: 1.4;">
                            ================================
                            PONDOK PESANTREN MAMBA'UL
                            HUDA
                            PAJOMBLANGAN
                            ================================
                            SURAT IZIN SEKOLAH
                            NO: ---/SKA.001/PPMH/-/----
                            --------------------------------

                            Kepada Yth.
                            Bapak/Ibu Guru ...

                            Assalamu'alaikum Wr. Wb.

                            Dengan hormat, melalui surat
                            ini kami memberitahukan bahwa:

                            Nama : -
                            Kelas : -
                            Ket : Izin tidak mengikuti
                            KBM
                            Tanggal : -
                            Alasan : -

                            Demikian surat ini kami
                            sampaikan. Atas perhatian
                            Bapak/Ibu, kami ucapkan
                            terima kasih.

                            Wassalamu'alaikum Wr. Wb.

                            Hormat kami,



                            Pengurus Izin
                            ================================
                        </div>
                    </div>
                </div>

                <!-- QZ Tray Status -->
                <div class="mt-3 p-2 rounded border" id="qz_status_container">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small">
                            <i class="fas fa-plug me-1"></i> Status Printer:
                            <span id="qz_status" class="fw-bold text-warning">Mengecek...</span>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkQzStatus()">
                            <i class="fas fa-sync-alt"></i> Cek Ulang
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4 fw-bold shadow-sm" id="btn_print_izin" disabled>
                    <i class="fas fa-print me-1"></i> CETAK SURAT
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
<script src="assets/js/qz-tray-config.js?v=<?= time() ?>"></script>
<script src="assets/js/aktivitas.js?v=<?= time() ?>"></script>
<?php include __DIR__ . '/include/footer.php'; ?>