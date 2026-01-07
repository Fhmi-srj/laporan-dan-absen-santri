<?php
/**
 * API: Aktivitas Siswa
 * Laporan Santri - PHP Murni
 * 
 * Endpoints:
 * GET  ?action=search&keyword=xxx - Search siswa
 * GET  ?action=data - DataTables server-side
 * GET  ?action=edit&id=xxx - Get single record
 * POST action=store - Create new aktivitas
 * POST action=update - Update aktivitas
 * POST action=delete - Single delete
 * POST action=bulk-delete - Bulk delete
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

// Check login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$pdo = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        // ============================================
        // Search Siswa
        // ============================================
        case 'search':
            $keyword = $_GET['keyword'] ?? '';
            if (strlen($keyword) < 3) {
                echo json_encode(['status' => 'error', 'pesan' => 'Ketik minimal 3 huruf']);
                exit;
            }

            // Search directly from data_induk
            $stmt = $pdo->prepare("
                SELECT id, nama_lengkap, nisn as nomor_induk, kelas, lembaga_sekolah as lembaga, no_wa_wali,
                       CONCAT(COALESCE(alamat,''), ', ', COALESCE(kecamatan,''), ', ', COALESCE(kabupaten,'')) as alamat
                FROM data_induk 
                WHERE deleted_at IS NULL AND (nama_lengkap LIKE ? OR no_wa_wali LIKE ? OR nisn LIKE ?)
                ORDER BY nama_lengkap ASC LIMIT 10
            ");
            $stmt->execute(["%$keyword%", "%$keyword%", "%$keyword%"]);
            $siswaList = $stmt->fetchAll();

            // Format result
            $siswa = [];
            foreach ($siswaList as $s) {
                $siswa[] = [
                    'id' => $s['id'],
                    'nama_lengkap' => $s['nama_lengkap'] ?? '-',
                    'nomor_induk' => $s['nomor_induk'] ?? null,
                    'kelas' => $s['kelas'] ?? null,
                    'lembaga' => $s['lembaga'] ?? '-',
                    'no_wa_wali' => $s['no_wa_wali'] ?? '-',
                ];
            }

            if (count($siswa) > 0) {
                echo json_encode(['status' => 'success', 'data' => $siswa]);
            } else {
                echo json_encode(['status' => 'error', 'pesan' => 'Siswa tidak ditemukan']);
            }
            break;

        // ============================================
        // Get Data for DataTables
        // ============================================
        case 'data':
            $kategori = $_GET['kategori'] ?? '';
            $tanggalDari = $_GET['tanggal_dari'] ?? '';
            $tanggalSampai = $_GET['tanggal_sampai'] ?? '';
            $searchKeyword = $_GET['search_keyword'] ?? '';
            $start = (int) ($_GET['start'] ?? 0);
            $length = (int) ($_GET['length'] ?? 10);
            $draw = (int) ($_GET['draw'] ?? 1);

            // Build query
            $where = ['ca.deleted_at IS NULL'];  // Only show non-deleted records
            $params = [];

            if ($kategori && $kategori !== 'all') {
                $where[] = "ca.kategori = ?";
                $params[] = $kategori;
            }

            if ($tanggalDari) {
                $where[] = "DATE(ca.tanggal) >= ?";
                $params[] = $tanggalDari;
            }

            if ($tanggalSampai) {
                $where[] = "DATE(ca.tanggal) <= ?";
                $params[] = $tanggalSampai;
            }

            // Search by judul, keterangan, status
            if ($searchKeyword) {
                $where[] = "(ca.judul LIKE ? OR ca.keterangan LIKE ? OR ca.status_kegiatan LIKE ?)";
                $params[] = "%$searchKeyword%";
                $params[] = "%$searchKeyword%";
                $params[] = "%$searchKeyword%";
            }

            // Role-based filtering
            if ($user['role'] === 'kesehatan') {
                $where[] = "ca.kategori = 'sakit'";
            }

            $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

            // Handle DataTables ordering
            $orderColumn = 'ca.tanggal';
            $orderDir = 'DESC';

            // Column mapping for sorting
            $columnMap = [
                0 => 'ca.id',
                1 => 'ca.tanggal',
                2 => 'ca.tanggal_selesai',
                3 => 'p.nama',
                4 => 'ca.kategori',
                5 => 'ca.judul',
                6 => 'ca.keterangan'
            ];

            if (isset($_POST['order'][0]['column']) && isset($_POST['order'][0]['dir'])) {
                $colIndex = (int) $_POST['order'][0]['column'];
                $dir = strtoupper($_POST['order'][0]['dir']) === 'ASC' ? 'ASC' : 'DESC';
                if (isset($columnMap[$colIndex])) {
                    $orderColumn = $columnMap[$colIndex];
                    $orderDir = $dir;
                }
            }

            $orderClause = "ORDER BY $orderColumn $orderDir";

            // Count total
            $countSql = "SELECT COUNT(*) FROM catatan_aktivitas ca JOIN data_induk di ON ca.siswa_id = di.id $whereClause";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalRecords = $countStmt->fetchColumn();

            // Get data - JOIN with data_induk
            $sql = "
                SELECT ca.*, di.nama_lengkap, di.nisn as nomor_induk, di.kelas, di.no_wa_wali,
                       u.name as pembuat_nama
                FROM catatan_aktivitas ca
                JOIN data_induk di ON ca.siswa_id = di.id
                LEFT JOIN users u ON ca.dibuat_oleh = u.id
                $whereClause
                $orderClause
                LIMIT $start, $length
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            // Format dates
            foreach ($data as &$row) {
                if ($row['tanggal']) {
                    $row['tanggal_formatted'] = date('d M Y H:i', strtotime($row['tanggal']));
                }
                if ($row['tanggal_selesai']) {
                    $row['tanggal_selesai_formatted'] = date('d M Y H:i', strtotime($row['tanggal_selesai']));
                }
                // Add siswa relation
                $row['siswa'] = [
                    'id' => $row['siswa_id'],
                    'nama_lengkap' => $row['nama_lengkap'],
                    'nomor_induk' => $row['nomor_induk'],
                    'kelas' => $row['kelas'],
                    'no_wa_wali' => $row['no_wa_wali']
                ];
                $row['pembuat'] = [
                    'name' => $row['pembuat_nama'] ?? 'System'
                ];
            }

            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
            break;

        // ============================================
        // Get Single Record for Edit
        // ============================================
        case 'edit':
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM catatan_aktivitas WHERE id = ?");
            $stmt->execute([$id]);
            $catatan = $stmt->fetch();

            if (!$catatan) {
                echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
                exit;
            }

            // Format for datetime-local input
            if ($catatan['tanggal']) {
                $catatan['tanggal'] = date('Y-m-d\TH:i', strtotime($catatan['tanggal']));
            }
            if ($catatan['batas_waktu']) {
                $catatan['batas_waktu'] = date('Y-m-d\TH:i', strtotime($catatan['batas_waktu']));
            }
            if ($catatan['tanggal_selesai']) {
                $catatan['tanggal_selesai'] = date('Y-m-d\TH:i', strtotime($catatan['tanggal_selesai']));
            }

            echo json_encode($catatan);
            break;

        // ============================================
        // Store New Aktivitas
        // ============================================
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $siswaId = $_POST['siswa_id'] ?? null;
            $kategori = $_POST['kategori'] ?? null;
            $tanggal = $_POST['tanggal'] ?? null;

            if (!$siswaId || !$kategori || !$tanggal) {
                throw new Exception('Data tidak lengkap');
            }

            // Handle file uploads
            $foto1 = null;
            $foto2 = null;

            if (!empty($_FILES['foto_dokumen_1']['name'])) {
                $foto1 = uploadFile($_FILES['foto_dokumen_1'], 'bukti_aktivitas');
            }
            if (!empty($_FILES['foto_dokumen_2']['name'])) {
                $foto2 = uploadFile($_FILES['foto_dokumen_2'], 'bukti_aktivitas');
            }

            $stmt = $pdo->prepare("
                INSERT INTO catatan_aktivitas 
                (siswa_id, kategori, judul, keterangan, status_sambangan, status_kegiatan, tanggal, batas_waktu, tanggal_selesai, foto_dokumen_1, foto_dokumen_2, dibuat_oleh, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $siswaId,
                $kategori,
                $_POST['judul'] ?? null,
                $_POST['keterangan'] ?? null,
                $_POST['status_sambangan'] ?? null,
                $_POST['status_kegiatan'] ?? null,
                $tanggal,
                $_POST['batas_waktu'] ?? null,
                $_POST['tanggal_selesai'] ?? null,
                $foto1,
                $foto2,
                $user['id']
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Data aktivitas berhasil disimpan!']);
            break;

        // ============================================
        // Update Aktivitas
        // ============================================
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $id = $_POST['log_id'] ?? null;
            if (!$id) {
                throw new Exception('ID tidak ditemukan');
            }

            // Get existing record
            $stmt = $pdo->prepare("SELECT * FROM catatan_aktivitas WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch();

            if (!$existing) {
                throw new Exception('Data tidak ditemukan');
            }

            // Handle file uploads
            $foto1 = $existing['foto_dokumen_1'];
            $foto2 = $existing['foto_dokumen_2'];

            if (!empty($_FILES['foto_dokumen_1']['name'])) {
                // Delete old file
                if ($foto1 && file_exists(__DIR__ . '/../uploads/' . $foto1)) {
                    unlink(__DIR__ . '/../uploads/' . $foto1);
                }
                $foto1 = uploadFile($_FILES['foto_dokumen_1'], 'bukti_aktivitas');
            }
            if (!empty($_FILES['foto_dokumen_2']['name'])) {
                if ($foto2 && file_exists(__DIR__ . '/../uploads/' . $foto2)) {
                    unlink(__DIR__ . '/../uploads/' . $foto2);
                }
                $foto2 = uploadFile($_FILES['foto_dokumen_2'], 'bukti_aktivitas');
            }

            $stmt = $pdo->prepare("
                UPDATE catatan_aktivitas SET
                kategori = ?, judul = ?, keterangan = ?, status_sambangan = ?, status_kegiatan = ?,
                tanggal = ?, batas_waktu = ?, tanggal_selesai = ?, foto_dokumen_1 = ?, foto_dokumen_2 = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['kategori'],
                $_POST['judul'] ?? null,
                $_POST['keterangan'] ?? null,
                $_POST['status_sambangan'] ?? null,
                $_POST['status_kegiatan'] ?? null,
                $_POST['tanggal'],
                $_POST['batas_waktu'] ?? null,
                $_POST['tanggal_selesai'] ?? null,
                $foto1,
                $foto2,
                $id
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Data aktivitas berhasil diperbarui!']);
            break;

        // ============================================
        // Delete Single
        // ============================================
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID tidak ditemukan');
            }

            // Soft delete - move to trash (don't delete files yet)
            $pdo->prepare("UPDATE catatan_aktivitas SET deleted_at = NOW(), deleted_by = ? WHERE id = ?")->execute([$user['id'], $id]);
            logActivity('DELETE', 'catatan_aktivitas', $id, null, null, null, 'Hapus aktivitas ke trash');

            echo json_encode(['status' => 'success', 'message' => 'Data dipindahkan ke trash']);
            break;

        // ============================================
        // Bulk Delete
        // ============================================
        case 'bulk-delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            // Only admin can bulk delete
            if ($user['role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $ids = $_POST['ids'] ?? [];
            if (empty($ids)) {
                throw new Exception('Tidak ada data yang dipilih');
            }

            // Soft delete - move to trash (don't delete files yet)
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([date('Y-m-d H:i:s'), $user['id']], $ids);
            $pdo->prepare("UPDATE catatan_aktivitas SET deleted_at = ?, deleted_by = ? WHERE id IN ($placeholders)")->execute($params);
            logActivity('DELETE', 'catatan_aktivitas', null, null, null, null, 'Hapus ' . count($ids) . ' aktivitas ke trash');

            echo json_encode(['status' => 'success', 'message' => 'Data dipindahkan ke trash']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
