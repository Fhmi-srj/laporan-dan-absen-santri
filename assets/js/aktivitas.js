/**
 * Aktivitas Siswa - JavaScript
 * Laporan Santri - PHP Murni
 */

$(document).ready(function () {
    let table;
    const role = document.body.dataset.role || 'admin';
    const csrfToken = $('input[name="csrf_token"]').val();
    const isAdmin = (role === 'admin');

    // Column Definitions
    const colDefs = {
        'default': [
            { title: '<input type="checkbox" id="select-all">', data: 'id', width: '5%', orderable: false, render: (d, t, r) => `<input type="checkbox" class="form-check-input row-checkbox" value="${d}" data-phone="${r.no_wa_wali || ''}">` },
            { title: 'Tanggal', data: 'tanggal', render: formatTgl },
            { title: 'Siswa', data: 'nama_lengkap', render: (d, t, r) => renderSiswa(r) },
            { title: 'Kategori', data: 'kategori', render: (d) => `<span class="badge bg-light text-dark border">${d.replace('_', ' ')}</span>` },
            { title: 'Judul/Isi', data: 'judul' },
            { title: 'Keterangan', data: 'keterangan' },
            { title: 'Aksi', data: 'id', render: (d, t, r) => renderAksi(d, r) }
        ],
        'sakit': [
            { title: '<input type="checkbox" id="select-all">', data: 'id', orderable: false, render: (d, t, r) => `<input type="checkbox" class="form-check-input row-checkbox" value="${d}" data-phone="${r.no_wa_wali || ''}">` },
            { title: 'Tgl Sakit', data: 'tanggal', render: formatTgl },
            { title: 'Tgl Sembuh', data: 'tanggal_selesai', render: (d) => d ? formatTgl(d) : '<span class="badge bg-danger rounded-pill px-3">Belum Sembuh</span>' },
            { title: 'Siswa', data: 'nama_lengkap', render: (d, t, r) => renderSiswa(r) },
            { title: 'Diagnosa', data: 'judul' },
            {
                title: 'Status', data: 'status_kegiatan', render: (d) => {
                    let status = d || 'Belum Diperiksa';
                    let color = status === 'Sudah Diperiksa' ? 'bg-success' : 'bg-warning';
                    return `<span class="badge ${color} rounded-pill">${status}</span>`;
                }
            },
            { title: 'Aksi', data: 'id', render: (d, t, r) => renderAksi(d, r) }
        ],
        'izin_keluar': [
            { title: '<input type="checkbox" id="select-all">', data: 'id', orderable: false, render: (d, t, r) => `<input type="checkbox" class="form-check-input row-checkbox" value="${d}" data-phone="${r.no_wa_wali || ''}">` },
            { title: 'Waktu Pergi', data: 'tanggal', render: formatTgl },
            { title: 'Waktu Kembali', data: 'tanggal_selesai', render: (d) => d ? formatTgl(d) : '<span class="badge bg-warning rounded-pill px-3">Belum Kembali</span>' },
            { title: 'Siswa', data: 'nama_lengkap', render: (d, t, r) => renderSiswa(r) },
            { title: 'Keperluan', data: 'judul' },
            { title: 'Aksi', data: 'id', render: (d, t, r) => renderAksi(d, r) }
        ],
        'paket': [
            { title: '<input type="checkbox" id="select-all">', data: 'id', orderable: false, render: (d, t, r) => `<input type="checkbox" class="form-check-input row-checkbox" value="${d}" data-phone="${r.no_wa_wali || ''}">` },
            { title: 'Tgl Tiba', data: 'tanggal', render: formatTgl },
            { title: 'Tgl Terima', data: 'tanggal_selesai', render: formatTgl },
            { title: 'Siswa', data: 'nama_lengkap', render: (d, t, r) => renderSiswa(r) },
            { title: 'Isi Paket', data: 'judul' },
            { title: 'Foto', data: 'foto_dokumen_1', render: (d, t, r) => renderFotoPaket(d, r) },
            { title: 'Aksi', data: 'id', render: (d, t, r) => renderAksi(d, r) }
        ]
    };

    // Helper Functions
    function formatTgl(data) {
        if (!data) return '-';
        let d = new Date(data);
        return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
    }

    function renderSiswa(r) {
        return `<div class="fw-bold text-dark">${r.nama_lengkap}</div><small class="text-muted">${r.nomor_induk}</small>`;
    }

    function renderFotoPaket(d, r) {
        let html = '';
        if (d) html += `<a href="uploads/${d}" target="_blank" class="btn btn-sm btn-light border text-primary me-1" title="Foto Paket"><i class="fas fa-box-open"></i></a>`;
        if (r.foto_dokumen_2) html += `<a href="uploads/${r.foto_dokumen_2}" target="_blank" class="btn btn-sm btn-light border text-success" title="Foto Penerima"><i class="fas fa-user-check"></i></a>`;
        return html || '-';
    }

    function renderAksi(id, row) {
        let siswaName = (row.nama_lengkap || '').replace(/'/g, "");

        // Store row index for WA button to retrieve full data
        let btnEdit = `<button class="btn btn-sm btn-outline-warning btn-edit me-1" data-id="${id}" title="Edit"><i class="fas fa-pencil-alt"></i></button>`;
        let btnWa = `<button class="btn btn-sm btn-outline-success btn-wa-single me-1" data-id="${id}" title="Kirim WA ke Wali"><i class="fab fa-whatsapp"></i></button>`;

        if (isAdmin) {
            let btnDelete = `<button class="btn btn-sm btn-outline-danger btn-delete-single" data-id="${id}" data-name="${siswaName}" title="Hapus"><i class="fas fa-trash-alt"></i></button>`;
            return `<div class="d-flex">${btnEdit} ${btnWa} ${btnDelete}</div>`;
        }
        return `<div class="d-flex">${btnEdit} ${btnWa}</div>`;
    }

    // Initialize DataTable
    window.refreshTable = function () {
        let cat = $('#filter_kategori').val();
        let defs = colDefs[cat] || colDefs['default'];

        if ($.fn.DataTable.isDataTable('#table-aktivitas')) {
            $('#table-aktivitas').DataTable().destroy();
            $('#table-aktivitas').empty();
        }

        table = $('#table-aktivitas').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 10,
            lengthChange: false,
            info: true,
            ordering: true,
            order: [[1, 'desc']], // Default sort by tanggal descending
            dom: 'rt<"bottom"p><"clear">',
            ajax: {
                url: "api/aktivitas-data.php",
                type: "POST",
                data: function (d) {
                    d.kategori = (cat === 'all') ? '' : cat;
                    d.search_keyword = $('#filter_search').val();
                    d.tanggal_dari = $('#filter_tanggal_dari').val();
                    d.tanggal_sampai = $('#filter_tanggal_sampai').val();
                    d.csrf_token = csrfToken;
                },
                beforeSend: function () {
                    $('#btn-refresh i').addClass('fa-spin');
                },
                complete: function () {
                    $('#btn-refresh i').removeClass('fa-spin');
                },
                error: function (xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                    Swal.fire('Error', 'Gagal memuat data. Silakan refresh halaman.', 'error');
                }
            },
            columns: defs,
            drawCallback: function () {
                $('#select-all').prop('checked', false);
                toggleBulkActions();
            }
        });
    };

    // Category Click Handler
    window.handleCategoryClick = function (cat) {
        $('#filter_kategori').val(cat);
        refreshTable();

        let sid = $('#selected_siswa_id').val();
        if (sid) {
            bukaModal(cat);
        } else {
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
                .fire({ icon: 'info', title: `Menampilkan data: ${cat.toUpperCase().replace('_', ' ')}` });
        }
    };

    // Initialize
    refreshTable();
    $('#filter_kategori').on('change', refreshTable);
    $('#filter_tanggal_dari, #filter_tanggal_sampai').on('change', function () { table.draw(); });

    let searchTimer;
    $('#filter_search').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => table.draw(), 400);
    });

    // Bulk Actions
    $(document).on('click', '#select-all', function () {
        $('.row-checkbox').prop('checked', this.checked);
        toggleBulkActions();
    });
    $(document).on('click', '.row-checkbox', toggleBulkActions);

    function toggleBulkActions() {
        let count = $('.row-checkbox:checked').length;
        $('#selected-count').text(count);
        if (count > 0) {
            $('#bulk-actions').removeClass('d-none');

            // Check if all selected items have same category
            let categories = [];
            $('.row-checkbox:checked').each(function () {
                let rowData = table.row($(this).closest('tr')).data();
                if (rowData) categories.push(rowData.kategori);
            });
            let allSameCategory = categories.length > 0 && categories.every(c => c === categories[0]);

            if (allSameCategory) {
                $('#btn-bulk-report').prop('disabled', false).attr('title', 'Buat laporan untuk ' + count + ' data');
            } else {
                $('#btn-bulk-report').prop('disabled', true).attr('title', 'Pilih item dengan kategori yang sama');
            }
        } else {
            $('#bulk-actions').addClass('d-none');
        }
    }

    // Bulk Report - Generate Laporan
    $('#btn-bulk-report').click(function () {
        let selectedData = [];
        $('.row-checkbox:checked').each(function () {
            let rowData = table.row($(this).closest('tr')).data();
            if (rowData) selectedData.push(rowData);
        });
        if (selectedData.length === 0) return;

        let kategori = selectedData[0].kategori;
        let reportText = generateReport(kategori, selectedData);

        $('#report_count').text(selectedData.length);
        $('#report_category').text(kategori.replace('_', ' ').toUpperCase());
        $('#report_text').val(reportText);

        new bootstrap.Modal(document.getElementById('modalReport')).show();
    });

    // Generate Report Text
    function generateReport(kategori, data) {
        let now = new Date();
        let dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

        let headers = {
            'sakit': 'LAPORAN SANTRI SAKIT',
            'izin_keluar': 'LAPORAN IZIN KELUAR',
            'izin_pulang': 'LAPORAN IZIN PULANG',
            'sambangan': 'LAPORAN SAMBANGAN',
            'pelanggaran': 'LAPORAN PELANGGARAN',
            'paket': 'LAPORAN PAKET MASUK',
            'hafalan': 'LAPORAN HAFALAN'
        };

        let categoryLabels = {
            'sakit': 'santri sakit',
            'izin_keluar': 'santri izin keluar',
            'izin_pulang': 'santri izin pulang',
            'sambangan': 'sambangan',
            'pelanggaran': 'pelanggaran',
            'paket': 'paket masuk',
            'hafalan': 'hafalan'
        };

        let lines = [];
        lines.push(headers[kategori] || 'LAPORAN AKTIVITAS');
        lines.push('Tanggal: ' + dateStr);
        lines.push('');
        lines.push('Terdapat ' + data.length + ' ' + (categoryLabels[kategori] || 'data') + ':');
        lines.push('');

        data.forEach((item, idx) => {
            lines.push((idx + 1) + '. ' + (item.nama_lengkap || '-'));

            switch (kategori) {
                case 'sakit':
                    lines.push('   Tanggal Sakit: ' + formatDateTime(item.tanggal));
                    lines.push('   Tanggal Sembuh: ' + formatDateTime(item.tanggal_selesai));
                    lines.push('   Diagnosa: ' + (item.judul || '-'));
                    lines.push('   Status: ' + (item.status_kegiatan || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
                    break;
                case 'izin_keluar':
                    lines.push('   Tanggal Pergi: ' + formatDateTime(item.tanggal));
                    lines.push('   Batas Waktu: ' + formatDateTime(item.batas_waktu));
                    lines.push('   Tanggal Kembali: ' + formatDateTime(item.tanggal_selesai));
                    lines.push('   Keperluan: ' + (item.judul || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
                    break;
                case 'izin_pulang':
                    lines.push('   Tanggal Pergi: ' + formatDateTime(item.tanggal));
                    lines.push('   Batas Waktu: ' + formatDateTime(item.batas_waktu));
                    lines.push('   Tanggal Kembali: ' + formatDateTime(item.tanggal_selesai));
                    lines.push('   Alasan: ' + (item.judul || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
                    break;
                case 'sambangan':
                    lines.push('   Tanggal: ' + formatDateTime(item.tanggal));
                    lines.push('   Nama Penjenguk: ' + (item.judul || '-'));
                    lines.push('   Hubungan: ' + (item.status_sambangan || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
                    break;
                case 'pelanggaran':
                    lines.push('   Tanggal: ' + formatDateTime(item.tanggal));
                    lines.push('   Jenis Pelanggaran: ' + (item.judul || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
                    break;
                case 'paket':
                    lines.push('   Tanggal Tiba: ' + formatDateTime(item.tanggal));
                    lines.push('   Tanggal Terima: ' + formatDateTime(item.tanggal_selesai));
                    lines.push('   Isi Paket: ' + (item.judul || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
                    break;
                case 'hafalan':
                    lines.push('   Tanggal: ' + formatDateTime(item.tanggal));
                    lines.push('   Nama Kitab/Surat: ' + (item.judul || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
                    break;
                default:
                    lines.push('   Tanggal: ' + formatDateTime(item.tanggal));
                    lines.push('   Judul: ' + (item.judul || '-'));
                    lines.push('   Keterangan: ' + (item.keterangan || '-'));
            }
            lines.push('');
        });

        return lines.join('\n');
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        let d = new Date(dateStr);
        if (isNaN(d.getTime())) return '-';
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' +
            d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
    }

    // Copy Report Button
    $('#btn_copy_report').click(function () {
        let text = $('#report_text').val();
        navigator.clipboard.writeText(text).then(function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Teks laporan telah disalin ke clipboard',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(function () {
            // Fallback for older browsers
            $('#report_text').select();
            document.execCommand('copy');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Teks laporan telah disalin',
                timer: 2000,
                showConfirmButton: false
            });
        });
    });

    // WA Massal Personal - Show modal with preview
    $('#btn-bulk-wa').click(function () {
        let dataList = [];
        $('.row-checkbox:checked').each(function () {
            let rowData = table.row($(this).closest('tr')).data();
            if (rowData && rowData.no_wa_wali && rowData.no_wa_wali !== '-') {
                dataList.push(rowData);
            }
        });

        if (dataList.length === 0) {
            Swal.fire('Perhatian', 'Tidak ada nomor wali yang tersedia dari data terpilih', 'warning');
            return;
        }

        // Update count
        $('#bulk_wa_count').text(dataList.length);

        // Build recipient list HTML
        let listHtml = '';
        dataList.forEach((item, idx) => {
            listHtml += `<div class="d-flex align-items-center py-1 px-2 ${idx % 2 === 0 ? '' : 'bg-white'} rounded">
                <i class="fab fa-whatsapp text-success me-2"></i>
                <span class="fw-medium">${item.no_wa_wali}</span>
                <span class="text-muted ms-2">- ${item.nama_lengkap || '-'}</span>
            </div>`;
        });
        $('#bulk_wa_list').html(listHtml);

        // Generate preview using first item
        let previewMessage = generatePersonalMessage(dataList[0]);
        $('#bulk_wa_preview').text(previewMessage);

        // Store data list for sending
        $('#modalBulkWa').data('dataList', dataList);

        new bootstrap.Modal(document.getElementById('modalBulkWa')).show();
    });

    // Generate personal message for a single item
    function generatePersonalMessage(item) {
        // Time-based greeting
        let hour = new Date().getHours();
        let greeting = 'Selamat pagi';
        if (hour >= 11 && hour < 15) greeting = 'Selamat siang';
        else if (hour >= 15 && hour < 18) greeting = 'Selamat sore';
        else if (hour >= 18 || hour < 5) greeting = 'Selamat malam';

        let kategori = item.kategori;
        let nama = item.nama_lengkap || '-';

        // Category labels
        let kategoriLabels = {
            'sakit': 'Pemberitahuan Santri Sakit',
            'izin_keluar': 'Izin Keluar',
            'izin_pulang': 'Izin Pulang',
            'sambangan': 'Pemberitahuan Sambangan',
            'pelanggaran': 'Pemberitahuan Pelanggaran',
            'paket': 'Pemberitahuan Paket Masuk',
            'hafalan': 'Pemberitahuan Hafalan'
        };

        let lines = [];
        lines.push(`${greeting}, Bapak/Ibu Wali dari *${nama}*`);
        lines.push('');
        lines.push(`Berikut informasi ${kategoriLabels[kategori] || 'Aktivitas'} putra/putri Anda:`);
        lines.push('');

        // Category-specific fields
        switch (kategori) {
            case 'sakit':
                lines.push(`Tanggal Sakit: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Tanggal Sembuh: ${formatDateTimeWA(item.tanggal_selesai)}`);
                lines.push(`Diagnosa: ${item.judul || '-'}`);
                lines.push(`Status: ${item.status_kegiatan || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
                break;
            case 'izin_keluar':
                lines.push(`Tanggal Pergi: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Batas Waktu: ${formatDateTimeWA(item.batas_waktu)}`);
                lines.push(`Tanggal Kembali: ${formatDateTimeWA(item.tanggal_selesai)}`);
                lines.push(`Keperluan: ${item.judul || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
                break;
            case 'izin_pulang':
                lines.push(`Tanggal Pergi: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Batas Waktu: ${formatDateTimeWA(item.batas_waktu)}`);
                lines.push(`Tanggal Kembali: ${formatDateTimeWA(item.tanggal_selesai)}`);
                lines.push(`Alasan: ${item.judul || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
                break;
            case 'sambangan':
                lines.push(`Tanggal: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Nama Penjenguk: ${item.judul || '-'}`);
                lines.push(`Hubungan: ${item.status_sambangan || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
                break;
            case 'pelanggaran':
                lines.push(`Tanggal: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Jenis Pelanggaran: ${item.judul || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
                break;
            case 'paket':
                lines.push(`Tanggal Tiba: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Tanggal Terima: ${formatDateTimeWA(item.tanggal_selesai)}`);
                lines.push(`Isi Paket: ${item.judul || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
                break;
            case 'hafalan':
                lines.push(`Tanggal: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Nama Kitab/Surat: ${item.judul || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
                break;
            default:
                lines.push(`Tanggal: ${formatDateTimeWA(item.tanggal)}`);
                lines.push(`Judul: ${item.judul || '-'}`);
                lines.push(`Keterangan: ${item.keterangan || '-'}`);
        }

        lines.push('');
        lines.push('Terima kasih.');
        lines.push('Mambaul Huda');

        return lines.join('\n');
    }

    function formatDateTimeWA(dateStr) {
        if (!dateStr) return '-';
        let d = new Date(dateStr);
        if (isNaN(d.getTime())) return '-';
        let day = String(d.getDate()).padStart(2, '0');
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let year = d.getFullYear();
        let hours = String(d.getHours()).padStart(2, '0');
        let mins = String(d.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${mins}`;
    }

    // WA Massal Personal - Send to all phones via API
    $('#btn_send_bulk_wa').click(function () {
        let dataList = $('#modalBulkWa').data('dataList') || [];

        if (dataList.length === 0) {
            Swal.fire('Error', 'Tidak ada data yang tersedia', 'error');
            return;
        }

        // Close modal and remove backdrop properly
        let modalEl = document.getElementById('modalBulkWa');
        let modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        }
        // Force remove backdrop and body class
        setTimeout(() => {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        }, 300);

        // Show loading
        Swal.fire({
            title: 'Mengirim Pesan...',
            html: `Mengirim ke <b>0</b> dari <b>${dataList.length}</b> wali`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Send personalized message to each phone via API
        let sent = 0;
        let failed = 0;
        let promises = dataList.map((item, idx) => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    let personalMessage = generatePersonalMessage(item);
                    // Build request data
                    let requestData = {
                        csrf_token: csrfToken,
                        phone: item.no_wa_wali,
                        message: personalMessage
                    };

                    // For paket category, include image
                    if (item.kategori === 'paket' && item.foto_dokumen_1) {
                        requestData.image = item.foto_dokumen_1;
                    }

                    $.post('api/send-wa.php', requestData).done(function (res) {
                        if (res.status === 'success') {
                            sent++;
                        } else {
                            failed++;
                        }
                    }).fail(function () {
                        failed++;
                    }).always(function () {
                        Swal.update({
                            html: `Mengirim ke <b>${sent + failed}</b> dari <b>${dataList.length}</b> wali`
                        });
                        resolve();
                    });
                }, idx * 500); // Delay between each
            });
        });

        Promise.all(promises).then(() => {
            Swal.fire({
                icon: failed === 0 ? 'success' : 'warning',
                title: 'Selesai!',
                html: `Berhasil mengirim ke <b>${sent}</b> wali` + (failed > 0 ? `<br>Gagal: <b>${failed}</b>` : ''),
                confirmButtonText: 'OK'
            });
        });
    });

    // Bulk Delete
    $('#btn-bulk-delete').click(function () {
        let ids = [];
        $('.row-checkbox:checked').each(function () { ids.push($(this).val()); });
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Hapus Data?',
            text: `${ids.length} data terpilih`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((r) => {
            if (r.isConfirmed) {
                $.post("api/bulk-delete.php", { csrf_token: csrfToken, ids: ids })
                    .done(function () { table.draw(); Swal.fire('Sukses', 'Data berhasil dihapus', 'success'); })
                    .fail(function () { Swal.fire('Error', 'Gagal menghapus data', 'error'); });
            }
        });
    });

    // Single Delete
    $(document).on('click', '.btn-delete-single', function () {
        let id = $(this).data('id');
        let name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Data?',
            text: `Data aktivitas "${name}" akan dihapus`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((r) => {
            if (r.isConfirmed) {
                $.post("api/bulk-delete.php", { csrf_token: csrfToken, ids: [id] })
                    .done(function () { table.draw(); Swal.fire('Sukses', 'Data berhasil dihapus', 'success'); })
                    .fail(function () { Swal.fire('Error', 'Gagal menghapus data', 'error'); });
            }
        });
    });

    // Single WA - Send personalized message to single wali
    $(document).on('click', '.btn-wa-single', function () {
        let id = $(this).data('id');
        let btn = $(this);

        // Get row data from DataTable
        let rowData = table.row(btn.closest('tr')).data();

        if (!rowData) {
            Swal.fire('Error', 'Data tidak ditemukan', 'error');
            return;
        }

        if (!rowData.no_wa_wali || rowData.no_wa_wali === '-') {
            Swal.fire('Perhatian', 'Nomor WA wali tidak tersedia', 'warning');
            return;
        }

        // Generate personalized message
        let message = generatePersonalMessage(rowData);

        // Confirm before sending
        Swal.fire({
            title: 'Kirim WA ke Wali?',
            html: `Kirim pemberitahuan ke <b>${rowData.no_wa_wali}</b><br><small class="text-muted">Wali dari ${rowData.nama_lengkap}</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25D366',
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Kirim',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Mengirim...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // Build request data
                let requestData = {
                    csrf_token: csrfToken,
                    phone: rowData.no_wa_wali,
                    message: message
                };

                // For paket category, include image
                if (rowData.kategori === 'paket' && rowData.foto_dokumen_1) {
                    requestData.image = rowData.foto_dokumen_1;
                }

                // Send via API
                $.post('api/send-wa.php', requestData).done(function (res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Pesan berhasil dikirim ke wali',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat mengirim pesan', 'error');
                    }
                }).fail(function (xhr) {
                    let errorMsg = 'Gagal mengirim pesan';
                    try {
                        let res = JSON.parse(xhr.responseText);
                        if (res.message) errorMsg = res.message;
                    } catch (e) { }
                    Swal.fire('Error', errorMsg, 'error');
                });
            }
        });
    });

    // Search Siswa
    $('#input_cari').on('keyup', function () {
        let kw = $(this).val();
        if (kw.length < 3) { $('#hasil_autocomplete').addClass('d-none'); return; }
        $.get("api/cari-siswa.php", { keyword: kw }, function (res) {
            if (res.status == 'success') {
                let html = '';
                res.data.forEach(s => {
                    html += `<a href="#" class="list-group-item list-group-item-action search-result-item" 
                            data-id="${s.id}" data-nama="${s.nama_lengkap}" data-nis="${s.nomor_induk}" 
                            data-kelas="${s.kelas}" data-alamat="${s.alamat || ''}" data-phone="${s.no_wa_wali || ''}">
                            <div class="fw-bold">${s.nama_lengkap}</div>
                            <div class="small text-muted">${s.kelas} | ${s.nomor_induk}</div>
                        </a>`;
                });
                $('#hasil_autocomplete').html(html).removeClass('d-none');
            }
        });
    });

    $(document).on('click', '.search-result-item', function (e) {
        e.preventDefault();
        let d = $(this).data();
        pilihSiswa(d.id, d.nama, d.nis, d.kelas, d.alamat, d.phone);
    });

    window.pilihSiswa = function (id, nama, nis, kelas, alamat, phone) {
        $('#selected_siswa_id').val(id);
        $('#selected_siswa_phone').val(phone);
        $('#lbl_nama').text(nama);
        $('#lbl_nis').text(nis);
        $('#lbl_kelas').text(kelas);
        $('#lbl_alamat').text(alamat);
        $('#empty_card').addClass('d-none');
        $('#card_siswa').removeClass('d-none');
        $('#hasil_autocomplete').addClass('d-none');
        $('#input_cari').val('');
    };

    $('#btn_reset').click(function () {
        $('#selected_siswa_id').val('');
        $('#empty_card').removeClass('d-none');
        $('#card_siswa').addClass('d-none');
        $('#input_cari').val('');
    });

    // Modal Input
    window.bukaModal = function (cat, editData = null) {
        if (!editData && !$('#selected_siswa_id').val()) {
            Swal.fire('Pilih Siswa', 'Silakan pilih siswa dulu untuk input data.', 'info');
            return;
        }

        $('#formAktivitas')[0].reset();
        resetFotoPreview();
        $('#group_sambangan, #group_tanggal_selesai, #group_status_sakit, #group_batas_waktu, #group_status_paket').addClass('d-none');
        $('#input_batas_waktu').prop('required', false).val('');
        $('#input_foto_1').prop('required', false);
        $('#select_status_paket').prop('disabled', false).val('Belum Diterima');
        $('#col_foto_2').addClass('d-none');
        $('#col_foto_1').removeClass('col-md-6').addClass('col-12');
        $('#col_tanggal_mulai').removeClass('col-md-4').addClass('col-md-6');
        $('#group_judul').removeClass('d-none');
        $('#lbl_tanggal_mulai').text('TANGGAL MULAI');
        $('#lbl_tanggal_selesai').text('TANGGAL SELESAI');
        $('#lbl_foto_1').html('FOTO BUKTI <span class="text-muted fw-normal">(Opsional)</span>');

        let title = "INPUT DATA", lbl = "JUDUL";
        if (cat == 'sakit') {
            title = "SAKIT"; lbl = "DIAGNOSA";
            $('#lbl_tanggal_mulai').text('TANGGAL SAKIT');
            $('#group_tanggal_selesai').removeClass('d-none');
            $('#lbl_tanggal_selesai').text('TANGGAL SEMBUH');
            $('#group_status_sakit').removeClass('d-none');
            $('#lbl_foto_1').html('FOTO SURAT DOKTER <span class="text-muted fw-normal">(Opsional)</span>');
        }
        else if (cat == 'izin_keluar') {
            title = "IZIN KELUAR"; lbl = "KEPERLUAN";
            $('#col_tanggal_mulai').removeClass('col-md-6').addClass('col-md-4');
            $('#lbl_tanggal_mulai').text('TANGGAL PERGI');
            $('#group_batas_waktu').removeClass('d-none');
            $('#input_batas_waktu').prop('required', true);
            $('#group_tanggal_selesai').removeClass('d-none');
            $('#lbl_tanggal_selesai').text('TANGGAL KEMBALI');
            $('#lbl_foto_1').html('FOTO SURAT IZIN <span class="text-muted fw-normal">(Opsional)</span>');
        }
        else if (cat == 'izin_pulang') {
            title = "IZIN PULANG"; lbl = "ALASAN";
            $('#col_tanggal_mulai').removeClass('col-md-6').addClass('col-md-4');
            $('#group_batas_waktu').removeClass('d-none');
            $('#input_batas_waktu').prop('required', true);
            $('#group_tanggal_selesai').removeClass('d-none');
            $('#lbl_tanggal_selesai').text('TGL KEMBALI');
            $('#lbl_foto_1').html('FOTO SURAT IZIN <span class="text-muted fw-normal">(Opsional)</span>');
        }
        else if (cat == 'sambangan') {
            title = "SAMBANGAN"; lbl = "NAMA PENJENGUK";
            $('#group_sambangan').removeClass('d-none');
            $('#lbl_foto_1').html('FOTO PENJENGUK <span class="text-muted fw-normal">(Opsional)</span>');
        }
        else if (cat == 'paket') {
            title = "PAKET"; lbl = "ISI PAKET";
            $('#group_status_paket').removeClass('d-none');
            $('#group_tanggal_selesai').removeClass('d-none');
            $('#lbl_tanggal_selesai').text('TGL TERIMA');

            if (editData) {
                // Edit mode - allow status change
                $('#select_status_paket').prop('disabled', false);
                if (editData.status_kegiatan === 'Sudah Diterima') {
                    $('#lbl_foto_1').html('FOTO PENERIMA+PAKET <span class="text-muted fw-normal">(Opsional jika tidak diubah)</span>');
                } else {
                    $('#lbl_foto_1').html('FOTO PAKET <span class="text-muted fw-normal">(Opsional jika tidak diubah)</span>');
                }
            } else {
                // Create mode - lock status to Belum Diterima
                $('#select_status_paket').val('Belum Diterima').prop('disabled', true);
                $('#lbl_foto_1').html('FOTO PAKET <span class="text-danger">*</span>');
                $('#input_foto_1').prop('required', true);
                // Hide tanggal terima for new paket
                $('#group_tanggal_selesai').addClass('d-none');
            }
        }
        else if (cat == 'pelanggaran') {
            title = "PELANGGARAN"; lbl = "JENIS PELANGGARAN";
            $('#lbl_foto_1').html('FOTO BUKTI <span class="text-muted fw-normal">(Opsional)</span>');
        }
        else if (cat == 'hafalan') {
            title = "HAFALAN"; lbl = "NAMA KITAB/SURAT";
            $('#lbl_foto_1').html('FOTO BUKTI HAFALAN <span class="text-muted fw-normal">(Opsional)</span>');
        }

        $('#modalTitle').text(editData ? `EDIT DATA ${title}` : title);
        $('#lbl_judul').text(lbl);

        if (editData) {
            $('#formAktivitas').attr('action', 'api/aktivitas-update.php');
            $('#modal_log_id').val(editData.id);
            $('#modal_siswa_id').val(editData.siswa_id);
            $('#modal_kategori').val(editData.kategori);
            $('#input_tanggal').val(editData.tanggal ? editData.tanggal.replace(' ', 'T').substr(0, 16) : '');
            $('#input_batas_waktu').val(editData.batas_waktu ? editData.batas_waktu.replace(' ', 'T').substr(0, 16) : '');
            $('#input_tanggal_selesai').val(editData.tanggal_selesai ? editData.tanggal_selesai.replace(' ', 'T').substr(0, 16) : '');
            $('#input_judul').val(editData.judul);
            $('#select_status_sambangan').val(editData.status_sambangan);
            $('#select_status_kegiatan').val(editData.status_kegiatan || 'Belum Diperiksa');
            // For paket category, use status_kegiatan for status_paket
            if (editData.kategori === 'paket') {
                $('#select_status_paket').val(editData.status_kegiatan || 'Belum Diterima');
            }
            $('#textarea_keterangan').val(editData.keterangan);

            if (editData.foto_dokumen_1) $('#preview_foto_1').attr('src', 'uploads/' + editData.foto_dokumen_1).removeClass('d-none');
            if (editData.foto_dokumen_2) $('#preview_foto_2').attr('src', 'uploads/' + editData.foto_dokumen_2).removeClass('d-none');
        } else {
            $('#formAktivitas').attr('action', 'api/aktivitas-store.php');
            $('#modal_siswa_id').val($('#selected_siswa_id').val());
            $('#modal_kategori').val(cat);
            $('#modal_log_id').val('');
            let now = new Date();
            $('#input_tanggal').val(now.toISOString().slice(0, 16));
        }

        new bootstrap.Modal(document.getElementById('modalInput')).show();
    };

    // Status Paket change handler - update UI based on status
    $('#select_status_paket').change(function () {
        let status = $(this).val();
        if (status === 'Sudah Diterima') {
            // Show tanggal terima and make it required
            $('#group_tanggal_selesai').removeClass('d-none');
            $('#input_tanggal_selesai').prop('required', true);
            $('#lbl_foto_1').html('FOTO PENERIMA+PAKET <span class="text-danger">*</span>');
            $('#input_foto_1').prop('required', true);
        } else {
            // Hide tanggal terima for Belum Diterima
            $('#group_tanggal_selesai').addClass('d-none');
            $('#input_tanggal_selesai').prop('required', false).val('');
            $('#lbl_foto_1').html('FOTO PAKET <span class="text-muted fw-normal">(Opsional)</span>');
            $('#input_foto_1').prop('required', false);
        }
    });

    // Edit Button
    $(document).on('click', '.btn-edit', function () {
        let id = $(this).data('id');
        let btn = $(this), original = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        $.get(`api/aktivitas-edit.php?id=${id}`, function (data) {
            window.bukaModal(data.kategori, data);
        }).always(function () { btn.html(original).prop('disabled', false); });
    });

    // Form Submit
    $('#formAktivitas').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        let action = $(this).attr('action');

        $.ajax({
            url: action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: res.message.replace(/\\n/g, '<br>'),
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }
                bootstrap.Modal.getInstance(document.getElementById('modalInput')).hide();
                table.draw();
                Swal.fire('Sukses', 'Data berhasil disimpan!', 'success');
            },
            error: function (xhr) {
                let errMsg = 'Gagal menyimpan data';
                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) errMsg = res.message.replace(/\\n/g, '<br>');
                } catch (e) { }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errMsg,
                    confirmButtonColor: '#3b82f6'
                });
            }
        });
    });

    // WhatsApp Handlers
    let currentWaPhone = '';

    $(document).on('click', '.btn-wa', function () {
        let msg = $(this).data('msg');
        currentWaPhone = $(this).data('phone') || '';
        $('#single_wa_message').val(msg);
        new bootstrap.Modal(document.getElementById('modalSendWa')).show();
    });

    $('#btn_send_single_wa').click(function () {
        let msg = $('#single_wa_message').val();
        if (!msg) { Swal.fire('Error', 'Pesan harus diisi', 'error'); return; }
        if (!currentWaPhone) { Swal.fire('Error', 'Nomor WA wali tidak tersedia', 'error'); return; }

        let btn = $(this);
        let originalHtml = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm"></span> Mengirim...').prop('disabled', true);

        $.post('api/send-wa.php', { phone: currentWaPhone, message: msg })
            .done(function (res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil!', 'Pesan WhatsApp telah terkirim ke wali siswa', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalSendWa')).hide();
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal mengirim pesan', 'error');
                }
            })
            .fail(function (xhr) {
                let errMsg = 'Gagal mengirim pesan';
                try { errMsg = JSON.parse(xhr.responseText).message; } catch (e) { }
                Swal.fire('Error', errMsg, 'error');
            })
            .always(function () {
                btn.html(originalHtml).prop('disabled', false);
            });
    });

    $('#btn-bulk-wa').click(function () {
        let selectedRows = [];
        $('.row-checkbox:checked').each(function () {
            let tr = $(this).closest('tr');
            let rowData = table.row(tr).data();
            selectedRows.push(rowData);
        });

        if (selectedRows.length === 0) { Swal.fire('Info', 'Pilih minimal satu data.', 'warning'); return; }

        let today = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        let message = `*LAPORAN AKTIVITAS SISWA*\n${today}\n\n`;

        let groupedData = {};
        selectedRows.forEach(row => {
            let cat = row.kategori;
            if (!groupedData[cat]) groupedData[cat] = [];
            groupedData[cat].push(row);
        });

        for (let cat in groupedData) {
            message += `*DATA ${cat.toUpperCase().replace(/_/g, ' ')}*\n`;
            groupedData[cat].forEach((row, i) => {
                message += `${i + 1}. *${row.nama_lengkap}* (${row.kelas || '-'})\n`;
                message += `   ${row.judul || '-'} ${row.keterangan ? '| ' + row.keterangan : ''}\n`;
            });
            message += `\n`;
        }

        $('#bulk_wa_count').text(selectedRows.length);
        $('#bulk_wa_message').val(message);
        new bootstrap.Modal(document.getElementById('modalBulkWa')).show();
    });

    $('#btn_send_bulk_confirm').click(function () {
        let msg = $('#bulk_wa_message').val();
        if (!msg) { Swal.fire('Error', 'Pesan tidak boleh kosong', 'error'); return; }
        window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`, '_blank');
        bootstrap.Modal.getInstance(document.getElementById('modalBulkWa')).hide();
    });

    // Utilities
    function resetFotoPreview() {
        $('.foto-preview').addClass('d-none').attr('src', '');
    }

    window.previewFoto = function (input, pid) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) { $('#' + pid).attr('src', e.target.result).removeClass('d-none'); };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // QR Scanner
    let scanner = null;
    $('#btn_buka_kamera').click(function () {
        $('#area_kamera').removeClass('d-none');
        scanner = new Html5Qrcode("reader");
        scanner.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, (txt) => {
            scanner.stop();
            $('#area_kamera').addClass('d-none');
            $.get("api/cari-siswa.php", { keyword: txt }, function (res) {
                if (res.status == 'success' && res.data.length > 0) {
                    let s = res.data[0];
                    pilihSiswa(s.id, s.nama_lengkap, s.nomor_induk, s.kelas, s.alamat, s.no_wa_wali);
                    Swal.fire({ icon: 'success', title: 'Siswa Ditemukan', text: s.nama_lengkap, timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire('Error', 'Siswa tidak ditemukan', 'error');
                }
            });
        }, () => { }).catch(function (err) {
            Swal.fire('Error Kamera', 'Tidak dapat mengakses kamera.', 'error');
            $('#area_kamera').addClass('d-none');
        });
    });

    $('#btn_tutup_kamera').click(function () {
        if (scanner) scanner.stop().catch(() => { });
        $('#area_kamera').addClass('d-none');
    });
});
