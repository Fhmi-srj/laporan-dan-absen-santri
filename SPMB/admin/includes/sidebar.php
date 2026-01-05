<?php
/**
 * Sidebar Component
 * 
 * Usage: Set $currentPage before including this file
 * Example: $currentPage = 'dashboard';
 */
?>
<!-- Mobile Header -->
<div class="md:hidden bg-primary text-white p-4 flex items-center justify-between fixed top-0 left-0 right-0 z-40">
    <button onclick="toggleSidebar()" class="text-xl"><i class="fas fa-bars"></i></button>
    <span class="font-bold">Admin SPMB</span>
    <a href="logout.php" class="text-xl"><i class="fas fa-sign-out-alt"></i></a>
</div>

<!-- Sidebar -->
<aside class="sidebar fixed md:sticky inset-y-0 left-0 z-50 w-64 bg-primary text-white h-screen flex flex-col">
    <div class="p-4 border-b border-white/10">
        <h1 class="font-bold text-lg">Admin SPMB</h1>
        <p class="text-xs text-white/60">Mambaul Huda</p>
    </div>

    <nav class="p-4 space-y-1 flex-1 overflow-y-auto">
        <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'dashboard' ? 'bg-white/10 text-white' : 'hover:bg-white/10 text-white/80 hover:text-white' ?> transition">
            <i class="fas fa-tachometer-alt w-5"></i><span>Dashboard</span>
        </a>
        <a href="pendaftaran.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'pendaftaran' ? 'bg-white/10 text-white' : 'hover:bg-white/10 text-white/80 hover:text-white' ?> transition">
            <i class="fas fa-users w-5"></i><span>Data Pendaftar</span>
        </a>
        <a href="biaya.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'biaya' ? 'bg-white/10 text-white' : 'hover:bg-white/10 text-white/80 hover:text-white' ?> transition">
            <i class="fas fa-money-bill w-5"></i><span>Biaya</span>
        </a>
        <a href="beasiswa.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'beasiswa' ? 'bg-white/10 text-white' : 'hover:bg-white/10 text-white/80 hover:text-white' ?> transition">
            <i class="fas fa-graduation-cap w-5"></i><span>Beasiswa</span>
        </a>
        <a href="kontak.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'kontak' ? 'bg-white/10 text-white' : 'hover:bg-white/10 text-white/80 hover:text-white' ?> transition">
            <i class="fas fa-phone-alt w-5"></i><span>Kontak</span>
        </a>
        <a href="pengaturan.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'pengaturan' ? 'bg-white/10 text-white' : 'hover:bg-white/10 text-white/80 hover:text-white' ?> transition">
            <i class="fas fa-cog w-5"></i><span>Pengaturan</span>
        </a>
        <a href="aktivitas.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'aktivitas' ? 'bg-white/10 text-white' : 'hover:bg-white/10 text-white/80 hover:text-white' ?> transition">
            <i class="fas fa-history w-5"></i><span>Log Aktivitas</span>
        </a>
    </nav>


    <div class="p-4 border-t border-white/10">
        <a href="profil.php"
            class="flex items-center gap-3 mb-3 hover:bg-white/10 p-2 -m-2 rounded-lg transition cursor-pointer">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-user"></i>
            </div>
            <div class="flex-1">
                <p class="font-medium text-sm"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?></p>
                <p class="text-xs text-white/60">Klik untuk edit profil</p>
            </div>
            <i class="fas fa-chevron-right text-white/40 text-xs"></i>
        </a>
        <a href="logout.php" class="block text-center py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition">
            <i class="fas fa-sign-out-alt mr-2"></i>Keluar
        </a>
    </div>
</aside>


<!-- Overlay for mobile -->
<div class="sidebar-overlay fixed inset-0 bg-black/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.querySelector('.sidebar-overlay').classList.toggle('hidden');
    }
</script>