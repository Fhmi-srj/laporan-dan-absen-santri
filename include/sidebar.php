<?php
/**
 * Sidebar Component
 * Multi-role menu with all features
 * Laporan Santri - PHP Murni
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdmin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$basePath = $isAdmin ? '../' : '';
$user = getCurrentUser();
$role = $user['role'] ?? '';

$roleLabels = [
    'admin' => 'Administrator',
    'karyawan' => 'Karyawan',
    'pengurus' => 'Pengurus',
    'guru' => 'Guru',
    'keamanan' => 'Keamanan',
    'kesehatan' => 'Kesehatan'
];
?>
<style>
    .sidebar {
        width: 250px;
        background: white;
        height: 100vh;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        position: fixed;
        left: 0;
        top: 0;
        padding-top: 60px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }

    .sidebar-scroll {
        flex: 1;
        overflow-y: auto;
        padding-bottom: 1rem;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0 1rem 1rem;
        margin: 0;
    }

    .sidebar-menu li {
        margin-bottom: 0.5rem;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #f1f5f9;
        color: var(--primary-color);
    }

    .sidebar-menu a i {
        width: 24px;
        margin-right: 10px;
    }

    .sidebar-divider {
        border-top: 1px solid #f1f5f9;
        margin: 1rem 0;
    }

    .sidebar-header {
        font-size: 0.7rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0 1rem;
        margin-bottom: 0.5rem;
    }

    .user-badge {
        background: linear-gradient(135deg, var(--primary-color), #a78bfa);
        color: white;
        padding: 1rem;
        margin: 1rem 1rem 0.5rem;
        border-radius: 12px;
        text-align: center;
        flex-shrink: 0;
    }

    .user-badge .role-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        display: inline-block;
        margin-top: 0.5rem;
    }

    @media (max-width: 991px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }

    /* Mobile offcanvas sidebar */
    .offcanvas-sidebar {
        width: 280px !important;
    }

    .offcanvas-sidebar .offcanvas-body {
        padding: 0;
    }

    .offcanvas-sidebar .user-badge-mobile {
        background: linear-gradient(135deg, var(--primary-color), #60a5fa);
        color: white;
        padding: 1.25rem;
        text-align: center;
    }

    .offcanvas-sidebar .user-badge-mobile .role-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        display: inline-block;
        margin-top: 0.5rem;
    }

    .offcanvas-sidebar .sidebar-menu-mobile {
        list-style: none;
        padding: 1rem;
        margin: 0;
    }

    .offcanvas-sidebar .sidebar-menu-mobile li {
        margin-bottom: 0.5rem;
    }

    .offcanvas-sidebar .sidebar-menu-mobile a {
        display: flex;
        align-items: center;
        padding: 0.875rem 1rem;
        border-radius: 10px;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 0.95rem;
    }

    .offcanvas-sidebar .sidebar-menu-mobile a:hover,
    .offcanvas-sidebar .sidebar-menu-mobile a.active {
        background: #f1f5f9;
        color: var(--primary-color);
    }

    .offcanvas-sidebar .sidebar-menu-mobile a i {
        width: 24px;
        margin-right: 12px;
        font-size: 1rem;
    }

    .offcanvas-sidebar .sidebar-divider-mobile {
        border-top: 1px solid #f1f5f9;
        margin: 0.75rem 0;
    }

    .offcanvas-sidebar .sidebar-header-mobile {
        font-size: 0.7rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0 1rem;
        margin-bottom: 0.5rem;
    }
</style>

<div class="sidebar">
    <div class="user-badge">
        <div class="fw-bold"><?= e($user['name'] ?? 'User') ?></div>
        <div class="role-badge"><?= $roleLabels[$role] ?? ucfirst($role) ?></div>
    </div>

    <div class="sidebar-scroll">
        <ul class="sidebar-menu">
            <!-- DASHBOARD - All Roles -->
            <li>
                <a href="<?= $basePath ?>dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-header">Absensi - Scan QR</div>

            <!-- SCANNER QR - All Roles -->
            <li>
                <a href="<?= $basePath ?>scanner.php" class="<?= $currentPage === 'scanner.php' ? 'active' : '' ?>">
                    <i class="fas fa-qrcode"></i> Scanner QR Code
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-header">Kartu RFID</div>

            <!-- RFID REGISTER - All Roles -->
            <li>
                <a href="<?= $basePath ?>rfid-register.php"
                    class="<?= $currentPage === 'rfid-register.php' ? 'active' : '' ?>">
                    <i class="fas fa-id-card"></i> Daftarkan Kartu
                </a>
            </li>


            <div class="sidebar-divider"></div>
            <div class="sidebar-header">Monitoring</div>

            <!-- LIVE ATTENDANCE - All Roles -->
            <li>
                <a href="<?= $basePath ?>live-attendance.php"
                    class="<?= $currentPage === 'live-attendance.php' ? 'active' : '' ?>">
                    <i class="fas fa-broadcast-tower"></i> Live Attendance
                </a>
            </li>

            <!-- HISTORY - All Roles -->
            <li>
                <a href="<?= $basePath ?>history.php" class="<?= $currentPage === 'history.php' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i> Riwayat Absensi
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <div class="sidebar-header">Santri</div>

            <!-- AKTIVITAS - All (except kesehatan only sees sakit) -->
            <li>
                <a href="<?= $basePath ?>aktivitas.php" class="<?= $currentPage === 'aktivitas.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <?= $role === 'kesehatan' ? 'Laporan Kesehatan' : 'Aktivitas Santri' ?>
                </a>
            </li>

            <!-- DATA SISWA - Admin, Guru, Pengurus -->
            <?php if (in_array($role, ['admin', 'guru', 'pengurus'])): ?>
                <li>
                    <a href="<?= $basePath ?>siswa.php" class="<?= $currentPage === 'siswa.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i> Data Santri
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-header">Admin Menu</div>

                <li>
                    <a href="<?= $basePath ?>admin/users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> Manajemen User
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/siswa.php"
                        class="<?= $currentPage === 'siswa.php' && $isAdmin ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i> Data Santri (Admin)
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>siswa-import.php"
                        class="<?= $currentPage === 'siswa-import.php' ? 'active' : '' ?>">
                        <i class="fas fa-file-import"></i> Import Santri
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/jadwal.php" class="<?= $currentPage === 'jadwal.php' ? 'active' : '' ?>">
                        <i class="fas fa-clock"></i> Jadwal Absen
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/attendances.php"
                        class="<?= $currentPage === 'attendances.php' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check"></i> Data Absensi
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/manual-attendance.php"
                        class="<?= $currentPage === 'manual-attendance.php' ? 'active' : '' ?>">
                        <i class="fas fa-edit"></i> Absensi Manual
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/report.php" class="<?= $currentPage === 'report.php' ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </li>
            <?php endif; ?>

            <!-- AKUN - All Roles (at bottom) -->
            <div class="sidebar-divider"></div>
            <div class="sidebar-header"><?= $role === 'admin' ? 'Pengaturan' : 'Akun' ?></div>

            <li>
                <a href="<?= $basePath ?>profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <?php if ($role === 'admin'): ?>
                <li>
                    <a href="<?= $basePath ?>admin/settings.php"
                        class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i> Pengaturan Sistem
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
    // Remember sidebar scroll position across page navigation
    (function () {
        const sidebar = document.querySelector('.sidebar-scroll');
        if (!sidebar) return;

        // Restore scroll position on page load
        const savedPos = sessionStorage.getItem('sidebarScroll');
        if (savedPos) {
            sidebar.scrollTop = parseInt(savedPos);
        }

        // Save scroll position before leaving page
        window.addEventListener('beforeunload', function () {
            sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
        });

        // Also save when clicking sidebar links
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function () {
                sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
            });
        });
    })();
</script>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="sidebarMobile">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" style="color: var(--primary-color);">
            <img src="<?= $basePath ?>logo-pondok.png" alt="Logo"
                style="height: 24px; width: auto; margin-right: 8px;"><?= APP_NAME ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="user-badge-mobile">
            <div class="fw-bold"><?= e($user['name'] ?? 'User') ?></div>
            <div class="role-badge"><?= $roleLabels[$role] ?? ucfirst($role) ?></div>
        </div>

        <ul class="sidebar-menu-mobile">
            <li>
                <a href="<?= $basePath ?>dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>


            <div class="sidebar-divider-mobile"></div>
            <div class="sidebar-header-mobile">Absensi - Scan QR</div>

            <li>
                <a href="<?= $basePath ?>scanner.php" class="<?= $currentPage === 'scanner.php' ? 'active' : '' ?>">
                    <i class="fas fa-qrcode"></i> Scanner QR Code
                </a>
            </li>

            <div class="sidebar-divider-mobile"></div>
            <div class="sidebar-header-mobile">Kartu RFID</div>

            <li>
                <a href="<?= $basePath ?>rfid-register.php"
                    class="<?= $currentPage === 'rfid-register.php' ? 'active' : '' ?>">
                    <i class="fas fa-id-card"></i> Daftarkan Kartu
                </a>
            </li>

            <div class="sidebar-divider-mobile"></div>
            <div class="sidebar-header-mobile">Monitoring</div>

            <li>
                <a href="<?= $basePath ?>live-attendance.php"
                    class="<?= $currentPage === 'live-attendance.php' ? 'active' : '' ?>">
                    <i class="fas fa-broadcast-tower"></i> Live Attendance
                </a>
            </li>
            <li>
                <a href="<?= $basePath ?>history.php" class="<?= $currentPage === 'history.php' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i> Riwayat Absensi
                </a>
            </li>

            <div class="sidebar-divider-mobile"></div>
            <div class="sidebar-header-mobile">Santri</div>

            <li>
                <a href="<?= $basePath ?>aktivitas.php" class="<?= $currentPage === 'aktivitas.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <?= $role === 'kesehatan' ? 'Laporan Kesehatan' : 'Aktivitas Santri' ?>
                </a>
            </li>
            <?php if (in_array($role, ['admin', 'guru', 'pengurus'])): ?>
                <li>
                    <a href="<?= $basePath ?>siswa.php" class="<?= $currentPage === 'siswa.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i> Data Santri
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <div class="sidebar-divider-mobile"></div>
                <div class="sidebar-header-mobile">Admin Menu</div>

                <li>
                    <a href="<?= $basePath ?>admin/users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
                        <i class="fas fa-users-cog"></i> Manajemen User
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/siswa.php"
                        class="<?= $currentPage === 'admin/siswa.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i> Data Santri (Admin)
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>siswa-import.php"
                        class="<?= $currentPage === 'siswa-import.php' ? 'active' : '' ?>">
                        <i class="fas fa-file-import"></i> Import Santri
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/jadwal.php" class="<?= $currentPage === 'jadwal.php' ? 'active' : '' ?>">
                        <i class="fas fa-clock"></i> Jadwal Absen
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/attendances.php"
                        class="<?= $currentPage === 'attendances.php' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check"></i> Data Absensi
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/manual-attendance.php"
                        class="<?= $currentPage === 'manual-attendance.php' ? 'active' : '' ?>">
                        <i class="fas fa-edit"></i> Absensi Manual
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/report.php" class="<?= $currentPage === 'report.php' ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </li>
            <?php endif; ?>

            <!-- AKUN - All Roles (at bottom) -->
            <div class="sidebar-divider-mobile"></div>
            <div class="sidebar-header-mobile"><?= $role === 'admin' ? 'Pengaturan' : 'Akun' ?></div>

            <li>
                <a href="<?= $basePath ?>profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <?php if ($role === 'admin'): ?>
                <li>
                    <a href="<?= $basePath ?>admin/settings.php"
                        class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i> Pengaturan Sistem
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>