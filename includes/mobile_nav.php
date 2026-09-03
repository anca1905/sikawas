<?php
// includes/mobile_nav.php
// Drop this at the END of every page's body, before </body>
// It injects: sidebar overlay, hamburger JS, and the bottom nav
// Usage: include ROOT_PATH . 'includes/mobile_nav.php';
// Requires: $currentPage, $role (set in parent page)

// Determine bottom nav items per role
$role = $_SESSION['role'] ?? '';
$page = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile: sidebar backdrop overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Mobile: Bottom Navigation -->
<nav class="bottom-nav" id="bottomNav">
    <div class="bottom-nav-inner">
        <?php if ($role === 'admin'): ?>
            <?php
            $db2 = getDB();
            $newC = $db2->query("SELECT COUNT(*) FROM pengaduan WHERE status='Baru'")->fetchColumn();
            ?>
            <a href="<?= BASE_URL ?>pages/admin/dashboard.php" class="<?= $page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Beranda</span>
            </a>
            <a href="<?= BASE_URL ?>pages/admin/laporan_baru.php" class="<?= $page === 'laporan_baru.php' ? 'active' : '' ?>">
                <?php if ($newC > 0): ?><span class="bnav-badge"><?= $newC ?></span><?php endif; ?>
                <i class="fas fa-bell"></i><span>Masuk</span>
            </a>
            <a href="<?= BASE_URL ?>pages/admin/verifikasi_laporan.php" class="<?= $page === 'verifikasi_laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-check"></i><span>Verifikasi</span>
            </a>
            <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php" class="<?= $page === 'kelola_laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-folder-open"></i><span>Laporan</span>
            </a>
            <a href="javascript:void(0)" onclick="toggleSidebar()">
                <i class="fas fa-ellipsis-h"></i><span>Lainnya</span>
            </a>

        <?php elseif ($role === 'masyarakat'): ?>
            <a href="<?= BASE_URL ?>pages/masyarakat/dashboard.php" class="<?= $page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Beranda</span>
            </a>
            <a href="<?= BASE_URL ?>pages/masyarakat/buat_laporan.php" class="<?= $page === 'buat_laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i><span>Laporan</span>
            </a>
            <a href="<?= BASE_URL ?>pages/masyarakat/riwayat_laporan.php" class="<?= $page === 'riwayat_laporan.php' || $page === 'detail_laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-history"></i><span>Riwayat</span>
            </a>
            <a href="<?= BASE_URL ?>pages/masyarakat/profil.php" class="<?= $page === 'profil.php' ? 'active' : '' ?>">
                <i class="fas fa-user"></i><span>Profil</span>
            </a>
            <a href="<?= BASE_URL ?>logout.php">
                <i class="fas fa-sign-out-alt"></i><span>Keluar</span>
            </a>

        <?php elseif ($role === 'polisi'): ?>
            <a href="<?= BASE_URL ?>pages/polisi/dashboard.php" class="<?= $page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Beranda</span>
            </a>
            <a href="<?= BASE_URL ?>pages/polisi/daftar_pengaduan.php" class="<?= $page === 'daftar_pengaduan.php' || $page === 'detail_pengaduan.php' ? 'active' : '' ?>">
                <i class="fas fa-list-ul"></i><span>Pengaduan</span>
            </a>
            <a href="<?= BASE_URL ?>logout.php">
                <i class="fas fa-sign-out-alt"></i><span>Keluar</span>
            </a>

        <?php elseif ($role === 'pimpinan'): ?>
            <a href="<?= BASE_URL ?>pages/pimpinan/dashboard.php" class="<?= $page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Beranda</span>
            </a>
            <a href="<?= BASE_URL ?>pages/pimpinan/rekap_laporan.php" class="<?= $page === 'rekap_laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i><span>Rekap</span>
            </a>
            <a href="<?= BASE_URL ?>logout.php">
                <i class="fas fa-sign-out-alt"></i><span>Keluar</span>
            </a>

        <?php endif; ?>
    </div>
</nav>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
        document.body.style.overflow = document.querySelector('.sidebar').classList.contains('open') ? 'hidden' : '';
    }

    function closeSidebar() {
        document.querySelector('.sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
        document.body.style.overflow = '';
    }
    // Close sidebar on nav link click (mobile)
    document.querySelectorAll('.nav-item').forEach(function(el) {
        el.addEventListener('click', function() {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
</script>