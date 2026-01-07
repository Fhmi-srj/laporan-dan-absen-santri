<?php
/**
 * Admin: Settings Page
 * Configure application settings
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Pengaturan';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key === 'action')
            continue;

        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$key, $value, $value]);
    }
    redirectWith('pengaturan.php', 'success', 'Pengaturan berhasil disimpan!');
}

// Get current settings
$stmt = $pdo->query("SELECT `key`, `value` FROM settings");
$settingsRaw = $stmt->fetchAll();
$settings = [];
foreach ($settingsRaw as $row) {
    $settings[$row['key']] = $row['value'];
}

// Defaults
$defaults = [
    'app_name' => 'Laporan Santri',
    'school_name' => 'Pondok Pesantren Mambaul Huda',
    'school_address' => '',
    'school_phone' => '',
    'wa_api_url' => 'http://serverwa.hello-inv.com/send-message',
    'wa_api_key' => '',
    'wa_sender' => '',
    'latitude' => '-7.2575',
    'longitude' => '112.7521',
    'radius_meters' => '100'
];

$settings = array_merge($defaults, $settings);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4"><i class="fas fa-cog me-2"></i>Pengaturan Aplikasi</h4>

    <form method="POST">
        <!-- General Settings -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-4"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Umum</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Aplikasi</label>
                    <input type="text" name="app_name" class="form-control" value="<?= e($settings['app_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Sekolah/Pondok</label>
                    <input type="text" name="school_name" class="form-control"
                        value="<?= e($settings['school_name']) ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="school_address" class="form-control"
                        value="<?= e($settings['school_address']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="school_phone" class="form-control"
                        value="<?= e($settings['school_phone']) ?>">
                </div>
            </div>
        </div>

        <!-- WhatsApp Settings -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-4"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Gateway</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">API URL</label>
                    <input type="url" name="wa_api_url" class="form-control" value="<?= e($settings['wa_api_url']) ?>">
                    <small class="text-muted">Contoh: http://serverwa.hello-inv.com/send-message</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">API Key</label>
                    <input type="text" name="wa_api_key" class="form-control" value="<?= e($settings['wa_api_key']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor Pengirim (Sender)</label>
                    <input type="text" name="wa_sender" class="form-control" value="<?= e($settings['wa_sender']) ?>"
                        placeholder="6281234567890">
                </div>
            </div>
        </div>

        <!-- Location Settings -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-4"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Lokasi Absensi</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" class="form-control" value="<?= e($settings['latitude']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" class="form-control" value="<?= e($settings['longitude']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Radius (meter)</label>
                    <input type="number" name="radius_meters" class="form-control"
                        value="<?= e($settings['radius_meters']) ?>">
                    <small class="text-muted">Jarak maksimal dari lokasi untuk absensi</small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save me-2"></i>Simpan Pengaturan
        </button>
    </form>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>