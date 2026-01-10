<?php
/**
 * Print Izin - Halaman untuk mencetak surat izin
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Print Izin';

include __DIR__ . '/include/header.php';
include __DIR__ . '/include/sidebar.php';
?>

<style>
    .tab-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .nav-tabs-custom {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border: none;
        padding: 0;
    }

    .nav-tabs-custom .nav-link {
        color: rgba(255, 255, 255, 0.7);
        border: none;
        border-radius: 0;
        padding: 18px 30px;
        font-weight: 600;
        transition: all 0.3s;
        position: relative;
    }

    .nav-tabs-custom .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .nav-tabs-custom .nav-link.active {
        background: white;
        color: #059669;
    }

    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: #059669;
        border-radius: 3px 3px 0 0;
    }

    .tab-content-area {
        padding: 30px;
    }

    .section-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .santri-list {
        max-height: 350px;
        overflow-y: auto;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .santri-item {
        display: flex;
        align-items: flex-start;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        transition: background-color 0.2s;
        cursor: pointer;
    }

    .santri-item:last-child {
        border-bottom: none;
    }

    .santri-item:hover {
        background-color: #f0fdf4;
    }

    .santri-item.selected {
        background-color: #dcfce7;
    }

    .santri-check {
        width: 22px;
        height: 22px;
        margin-right: 14px;
        margin-top: 2px;
        accent-color: #059669;
        cursor: pointer;
    }

    .preview-box {
        background: #1e293b;
        color: #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 0.75rem;
        line-height: 1.5;
        white-space: pre;
        min-height: 400px;
        max-height: 500px;
        overflow-y: auto;
        overflow-x: auto;
    }

    .form-label-custom {
        font-weight: 600;
        color: #374151;
        font-size: 0.85rem;
        margin-bottom: 8px;
    }

    .qz-status {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: #f8fafc;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    .qz-status .indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #94a3b8;
    }

    .qz-status .indicator.connected {
        background: #10b981;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .coming-soon {
        text-align: center;
        padding: 80px 20px;
    }

    .coming-soon i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 20px;
    }

    .coming-soon h4 {
        color: #64748b;
        margin-bottom: 10px;
    }

    .coming-soon p {
        color: #94a3b8;
    }

    .btn-print {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border: none;
        padding: 14px 28px;
        font-weight: 600;
        border-radius: 10px;
        color: white;
        transition: all 0.3s;
    }

    .btn-print:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        color: white;
    }

    .btn-print:disabled {
        background: #94a3b8;
        transform: none;
        box-shadow: none;
    }

    .category-radio {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .category-radio .btn-check:checked+.btn {
        background: #059669;
        color: white;
        border-color: #059669;
    }

    .category-radio .btn {
        flex: 1;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 500;
    }
</style>

<!-- Main Content -->
<div class="main-content">
    <!-- Header -->
    <div class="mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-print text-success me-2"></i>Print Izin</h4>
    </div>

    <!-- Tab Container -->
    <div class="tab-container">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom" id="izinTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="sekolah-tab" data-bs-toggle="tab" data-bs-target="#sekolah"
                    type="button" role="tab">
                    <i class="fas fa-school me-2"></i>Izin Sekolah
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pondok-tab" data-bs-toggle="tab" data-bs-target="#pondok" type="button"
                    role="tab">
                    <i class="fas fa-mosque me-2"></i>Izin Pondok
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Tab: Izin Sekolah -->
            <div class="tab-pane fade show active" id="sekolah" role="tabpanel">
                <div class="tab-content-area">
                    <div class="row g-4">
                        <!-- Left Column: Form -->
                        <div class="col-lg-7">
                            <!-- Category Selection -->
                            <div class="section-title">KATEGORI IZIN</div>
                            <div class="category-radio">
                                <input type="radio" class="btn-check" name="print_kategori" id="kat_sakit" value="sakit"
                                    checked>
                                <label class="btn btn-outline-secondary" for="kat_sakit">
                                    <i class="fas fa-thermometer-half me-1"></i> Sakit
                                </label>
                                <input type="radio" class="btn-check" name="print_kategori" id="kat_pulang"
                                    value="izin_pulang">
                                <label class="btn btn-outline-secondary" for="kat_pulang">
                                    <i class="fas fa-home me-1"></i> Izin Pulang
                                </label>
                            </div>

                            <!-- Santri List -->
                            <div class="section-title">PILIH SANTRI <span class="text-muted fw-normal">(Maks 5)</span>
                            </div>
                            <div class="santri-list mb-4" id="santri_list">
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-spinner fa-spin me-2"></i> Memuat data...
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="badge bg-success fs-6"><span id="selected_count">0</span>/5 santri
                                    dipilih</span>
                            </div>

                            <!-- Form Fields -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-custom">TUJUAN GURU <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="tujuan_guru" placeholder="Nama guru...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">KELAS <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kelas"
                                        placeholder="Contoh: VII, VIII, IX">
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Preview -->
                        <div class="col-lg-5">
                            <div class="section-title">PREVIEW SURAT</div>
                            <div class="preview-box" id="preview_box"></div>

                            <!-- QZ Status & Print Button -->
                            <div class="mt-4">
                                <div class="qz-status mb-3">
                                    <span class="indicator" id="qz_indicator"></span>
                                    <span>Status Printer: <strong id="qz_status_text">-</strong></span>
                                    <button class="btn btn-sm btn-outline-secondary ms-auto" onclick="checkQzStatus()">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <button class="btn btn-print w-100" id="btn_cetak" onclick="cetakSurat()" disabled>
                                    <i class="fas fa-print me-2"></i> CETAK SURAT
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Izin Pondok -->
            <div class="tab-pane fade" id="pondok" role="tabpanel">
                <div class="tab-content-area">
                    <div class="coming-soon">
                        <i class="fas fa-tools"></i>
                        <h4>Coming Soon</h4>
                        <p>Fitur Izin Pondok sedang dalam pengembangan.<br>Silakan gunakan Izin Sekolah untuk saat ini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
<script src="assets/js/qz-tray-config.js?v=<?= time() ?>"></script>
<script>
    $(document).ready(function () {
        let printData = {
            kategori: 'sakit',
            selectedSantri: [],
            nomorSurat: ''
        };

        // Load santri on page load
        loadSantri('sakit');
        updatePreview();
        checkQzStatus();

        // Category change
        $('input[name="print_kategori"]').change(function () {
            printData.kategori = $(this).val();
            printData.selectedSantri = [];
            $('#selected_count').text('0');
            loadSantri(printData.kategori);
            updatePreview();
            updatePrintButton();
        });

        // Load santri list
        function loadSantri(kategori) {
            $('#santri_list').html('<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</div>');

            $.get('api/print-izin.php', { kategori: kategori })
                .done(function (res) {
                    console.log('API response:', res);
                    if (res.success && res.data && res.data.length > 0) {
                        let html = '';
                        res.data.forEach(function (item, idx) {
                            let tanggal = item.tanggal ? new Date(item.tanggal).toLocaleDateString('id-ID') : '-';
                            html += '<div class="santri-item" data-id="' + item.siswa_id + '" data-nama="' + item.nama_lengkap + '" data-kelas="' + (item.kelas || '') + '">';
                            html += '  <input type="checkbox" class="santri-check" id="santri_' + item.aktivitas_id + '">';
                            html += '  <div class="flex-grow-1">';
                            html += '    <div class="d-flex justify-content-between align-items-center">';
                            html += '      <div><span class="fw-bold">' + item.nama_lengkap + '</span><span class="text-muted ms-2 small">' + (item.kelas || '-') + '</span></div>';
                            html += '      <small class="text-muted">' + tanggal + '</small>';
                            html += '    </div>';
                            html += '    <small class="text-muted">' + (item.judul || item.keterangan || '-') + '</small>';
                            html += '  </div>';
                            html += '</div>';
                        });
                        $('#santri_list').html(html);
                    } else {
                        $('#santri_list').html('<div class="text-center text-muted py-5"><i class="fas fa-inbox me-2"></i> Tidak ada data dalam 7 hari terakhir</div>');
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Load santri error:', error);
                    $('#santri_list').html('<div class="text-center text-danger py-5">Gagal memuat data: ' + error + '</div>');
                });
        }

        // Santri selection
        $(document).on('click', '.santri-item', function (e) {
            if ($(e.target).hasClass('santri-check')) return;
            $(this).find('.santri-check').click();
        });

        $(document).on('change', '.santri-check', function () {
            let item = $(this).closest('.santri-item');
            let id = item.data('id');
            let nama = item.data('nama');
            let kelas = item.data('kelas');

            if ($(this).is(':checked')) {
                if (printData.selectedSantri.length >= 5) {
                    $(this).prop('checked', false);
                    Swal.fire('Maksimal 5 Santri', 'Anda hanya dapat memilih maksimal 5 santri', 'warning');
                    return;
                }
                item.addClass('selected');
                printData.selectedSantri.push({ id: id, nama: nama, kelas: kelas });
            } else {
                item.removeClass('selected');
                printData.selectedSantri = printData.selectedSantri.filter(function (s) { return s.id !== id; });
            }

            $('#selected_count').text(printData.selectedSantri.length);

            // Auto-fill kelas
            if (printData.selectedSantri.length === 1 && printData.selectedSantri[0].kelas) {
                $('#kelas').val(printData.selectedSantri[0].kelas);
            }

            updatePreview();
            updatePrintButton();
        });

        // Input changes
        $('#tujuan_guru, #kelas').on('input', function () {
            updatePreview();
            updatePrintButton();
        });

        // Generate preview
        function updatePreview() {
            let now = new Date();
            let tanggal = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            let tujuanGuru = $('#tujuan_guru').val() || '...';
            let kelas = $('#kelas').val() || '-';
            let alasan = printData.kategori === 'sakit' ? 'Sakit' : 'Izin Pulang';

            let namaList = '-';
            if (printData.selectedSantri.length > 0) {
                namaList = printData.selectedSantri.map(function (s) { return s.nama; }).join('\n          ');
            }

            let month = now.getMonth();
            let romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            let previewNomor = '0XX/SKA.001/PPMH/' + romanMonths[month] + '/' + now.getFullYear();

            let preview = '================================\n';
            preview += '   PONDOK PESANTREN MAMBA\'UL   \n';
            preview += '           HUDA              \n';
            preview += '       PAJOMBLANGAN          \n';
            preview += '================================\n';
            preview += '      SURAT IZIN SEKOLAH      \n';
            preview += '   NO: ' + previewNomor + '\n';
            preview += '--------------------------------\n\n';
            preview += 'Kepada Yth.\n';
            preview += 'Bapak/Ibu Guru ' + tujuanGuru + '\n\n';
            preview += 'Assalamu\'alaikum Wr. Wb.\n\n';
            preview += 'Dengan hormat, melalui surat \n';
            preview += 'ini kami memberitahukan bahwa:\n\n';
            preview += 'Nama    : ' + namaList + '\n';
            preview += 'Kelas   : ' + kelas + '\n';
            preview += 'Ket     : Izin tidak mengikuti\n';
            preview += '          KBM\n';
            preview += 'Tanggal : ' + tanggal + '\n';
            preview += 'Alasan  : ' + alasan + '\n\n';
            preview += 'Demikian surat ini kami \n';
            preview += 'sampaikan. Atas perhatian \n';
            preview += 'Bapak/Ibu, kami ucapkan \n';
            preview += 'terima kasih.\n\n';
            preview += 'Wassalamu\'alaikum Wr. Wb.\n\n';
            preview += 'Hormat kami,\n\n\n\n';
            preview += 'Pengurus Izin\n';
            preview += '================================';

            $('#preview_box').text(preview);
        }

        // Update print button
        function updatePrintButton() {
            let canPrint = printData.selectedSantri.length > 0 &&
                $('#tujuan_guru').val().trim() !== '' &&
                $('#kelas').val().trim() !== '';
            $('#btn_cetak').prop('disabled', !canPrint);
        }

        // Check QZ Status
        window.checkQzStatus = function () {
            $('#qz_status_text').text('Mengecek...');
            $('#qz_indicator').removeClass('connected');

            if (typeof QzPrint !== 'undefined') {
                QzPrint.init().then(function (connected) {
                    if (connected) {
                        $('#qz_status_text').text('Terhubung');
                        $('#qz_indicator').addClass('connected');
                    } else {
                        $('#qz_status_text').text('Tidak Terhubung (Queue Mode)');
                    }
                }).catch(function (e) {
                    console.error('QZ error:', e);
                    $('#qz_status_text').text('Tidak Terhubung (Queue Mode)');
                });
            } else {
                $('#qz_status_text').text('QZ tidak tersedia (Queue Mode)');
            }
        };

        // Print function
        window.cetakSurat = function () {
            if (printData.selectedSantri.length === 0) {
                Swal.fire('Error', 'Pilih minimal 1 santri', 'error');
                return;
            }

            let tujuanGuru = $('#tujuan_guru').val().trim();
            let kelas = $('#kelas').val().trim();

            if (!tujuanGuru || !kelas) {
                Swal.fire('Error', 'Lengkapi field Tujuan Guru dan Kelas', 'error');
                return;
            }

            Swal.fire({
                title: 'Memproses...',
                html: 'Menggenerate nomor surat...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            // Generate nomor surat via API
            $.ajax({
                url: 'api/print-izin.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    kategori: printData.kategori,
                    santri_ids: printData.selectedSantri.map(function (s) { return s.id; }),
                    santri_names: printData.selectedSantri.map(function (s) { return s.nama; }),
                    tujuan_guru: tujuanGuru,
                    kelas: kelas,
                    tanggal: new Date().toISOString().split('T')[0]
                })
            }).done(function (response) {
                if (!response.success) {
                    Swal.fire('Error', response.message || 'Gagal generate nomor surat', 'error');
                    return;
                }

                printData.nomorSurat = response.nomor_surat;

                let now = new Date();
                let printJobData = {
                    nomorSurat: printData.nomorSurat,
                    tujuanGuru: tujuanGuru,
                    santriNames: printData.selectedSantri.map(function (s) { return s.nama; }),
                    kelas: kelas,
                    tanggal: now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }),
                    kategori: printData.kategori
                };

                // Check if QZ Tray available
                if (typeof QzPrint !== 'undefined' && QzPrint.isConnected()) {
                    Swal.update({ html: 'Mengirim ke printer...' });
                    QzPrint.print(printJobData).then(function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: 'Surat berhasil dicetak<br><strong>No: ' + printData.nomorSurat + '</strong>',
                            confirmButtonText: 'OK'
                        });
                        resetForm();
                    }).catch(function (e) {
                        Swal.fire('Error', e.message || 'Gagal mencetak', 'error');
                    });
                } else {
                    // Send to print queue
                    Swal.update({ html: 'Mengirim ke antrian print...' });

                    $.ajax({
                        url: 'api/print-queue.php?action=add',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            job_type: 'surat_izin',
                            job_data: printJobData
                        })
                    }).done(function (queueResponse) {
                        if (queueResponse.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dikirim ke Antrian!',
                                html: 'Surat akan dicetak oleh Print Server<br><strong>No: ' + printData.nomorSurat + '</strong>',
                                confirmButtonText: 'OK'
                            });
                            resetForm();
                        } else {
                            Swal.fire('Error', queueResponse.message || 'Gagal mengirim ke antrian', 'error');
                        }
                    }).fail(function () {
                        Swal.fire('Error', 'Gagal mengirim ke antrian', 'error');
                    });
                }
            }).fail(function (xhr) {
                console.error('Print error:', xhr);
                Swal.fire('Error', 'Gagal generate nomor surat', 'error');
            });
        };

        function resetForm() {
            printData.selectedSantri = [];
            $('#selected_count').text('0');
            $('#tujuan_guru').val('');
            $('#kelas').val('');
            $('.santri-item').removeClass('selected');
            $('.santri-check').prop('checked', false);
            updatePreview();
            updatePrintButton();
        }
    });
</script>

<?php include __DIR__ . '/include/footer.php'; ?>