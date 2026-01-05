<?php
require_once '../api/config.php';
requireLogin();

$conn = getConnection();
$message = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = substr($key, 8);
            $settingValue = sanitize($conn, $value);
            $stmt = $conn->prepare("UPDATE pengaturan SET nilai = ? WHERE kunci = ?");
            $stmt->bind_param("ss", $settingValue, $settingKey);
            $stmt->execute();
        }
    }
    $message = 'Pengaturan berhasil disimpan!';
}

// Get all settings
$result = $conn->query("SELECT * FROM pengaturan ORDER BY id ASC");
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['kunci']] = $row;
}

$conn->close();

// Page config
$pageTitle = 'Pengaturan - Admin SPMB';
$currentPage = 'pengaturan';
?>
<?php include 'includes/header.php'; ?>
<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: #E67E22;
    }

    .toggle-checkbox:checked+.toggle-label {
        background-color: #E67E22;
    }
</style>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan</h2>
            <p class="text-gray-500 text-sm">Atur konfigurasi website</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <i class="fas fa-check-circle mr-2"></i><?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Status Pendaftaran -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-toggle-on mr-2 text-primary"></i>Status
                    Pendaftaran</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-700">Buka Pendaftaran</p>
                        <p class="text-sm text-gray-500">Jika dimatikan, form pendaftaran tidak bisa diakses</p>
                    </div>
                    <div class="relative inline-block w-12 align-middle select-none">
                        <input type="hidden" name="setting_status_pendaftaran" value="0">
                        <input type="checkbox" name="setting_status_pendaftaran" value="1"
                            <?= ($settings['status_pendaftaran']['nilai'] ?? '0') === '1' ? 'checked' : '' ?>
                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-all duration-200"
                            style="top: 0; right: 24px;">
                        <label
                            class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-200"></label>
                    </div>
                </div>
            </div>

            <!-- Tahun Ajaran -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-calendar mr-2 text-primary"></i>Tahun
                    Ajaran</h3>
                <input type="text" name="setting_tahun_ajaran"
                    value="<?= htmlspecialchars($settings['tahun_ajaran']['nilai'] ?? '') ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
                    placeholder="2026/2027">
            </div>

            <!-- Jadwal Pendaftaran -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-clock mr-2 text-primary"></i>Jadwal
                    Pendaftaran</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="font-medium text-gray-700 mb-2">Gelombang 1</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-500">Mulai</label>
                                <input type="date" name="setting_gelombang_1_start"
                                    value="<?= $settings['gelombang_1_start']['nilai'] ?? '' ?>"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Selesai</label>
                                <input type="date" name="setting_gelombang_1_end"
                                    value="<?= $settings['gelombang_1_end']['nilai'] ?? '' ?>"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="font-medium text-gray-700 mb-2">Gelombang 2</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-500">Mulai</label>
                                <input type="date" name="setting_gelombang_2_start"
                                    value="<?= $settings['gelombang_2_start']['nilai'] ?? '' ?>"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Selesai</label>
                                <input type="date" name="setting_gelombang_2_end"
                                    value="<?= $settings['gelombang_2_end']['nilai'] ?? '' ?>"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link Download -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-link mr-2 text-primary"></i>Link Download
                    PDF</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link PDF Biaya</label>
                        <input type="url" name="setting_link_pdf_biaya"
                            value="<?= htmlspecialchars($settings['link_pdf_biaya']['nilai'] ?? '') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link PDF Brosur</label>
                        <input type="url" name="setting_link_pdf_brosur"
                            value="<?= htmlspecialchars($settings['link_pdf_brosur']['nilai'] ?? '') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link PDF Syarat</label>
                        <input type="url" name="setting_link_pdf_syarat"
                            value="<?= htmlspecialchars($settings['link_pdf_syarat']['nilai'] ?? '') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Info Beasiswa</label>
                        <input type="url" name="setting_link_beasiswa"
                            value="<?= htmlspecialchars($settings['link_beasiswa']['nilai'] ?? '') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    </div>
                </div>
            </div>

            <!-- Link Grup WhatsApp -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fab fa-whatsapp mr-2 text-green-600"></i>Grup
                    WhatsApp</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link Grup WA Pendaftar</label>
                    <input type="url" name="setting_link_grup_wa"
                        value="<?= htmlspecialchars($settings['link_grup_wa']['nilai'] ?? '') ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
                        placeholder="https://chat.whatsapp.com/...">
                    <p class="text-xs text-gray-500 mt-1">Link ini akan ditampilkan setelah pendaftar berhasil mendaftar
                    </p>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Simpan Pengaturan
            </button>
        </form>
    </main>
</div>

</body>

</html>