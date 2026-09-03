<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$uid  = $_SESSION['user_id'];
$nama = $_SESSION['nama'] ?? 'Admin';
$db   = getDB();
$newCount = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Baru'")->fetchColumn();
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-top">
            <div class="brand-logo"><span>🌿</span></div>
            <div>
                <h2>SIKAWAS</h2>
                <p>Sistem Informasi Pelaporan Kejahatan Lingkungan</p>
            </div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($nama, 0, 1)) ?></div>
        <div class="user-info">
            <div class="user-name"><?= sanitize($nama) ?></div>
            <div class="user-role"><i class="fas fa-user-shield"></i> Administrator</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu Utama</div>
        <a href="<?= BASE_URL ?>pages/admin/dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Beranda Dashboard
        </a>
        <a href="<?= BASE_URL ?>pages/admin/laporan_baru.php" class="nav-item <?= $currentPage === 'laporan_baru.php' ? 'active' : '' ?>">
            <i class="fas fa-bell"></i> Laporan Masuk
            <?php if ($newCount > 0): ?><span class="nav-badge"><?= $newCount ?></span><?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>pages/admin/verifikasi_laporan.php" class="nav-item <?= $currentPage === 'verifikasi_laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-check"></i> Verifikasi Laporan
        </a>
        <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php" class="nav-item <?= $currentPage === 'kelola_laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-folder-open"></i> Kelola Laporan
        </a>
        <a href="<?= BASE_URL ?>pages/admin/rekap_laporan.php" class="nav-item <?= $currentPage === 'rekap_laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Rekap Laporan
        </a>

        <div class="nav-section-title">Data Master</div>
        <a href="<?= BASE_URL ?>pages/admin/jenis_kejahatan.php" class="nav-item <?= $currentPage === 'jenis_kejahatan.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Jenis Kejahatan
        </a>
        <a href="<?= BASE_URL ?>pages/admin/kawasan_hutan.php" class="nav-item <?= $currentPage === 'kawasan_hutan.php' ? 'active' : '' ?>">
            <i class="fas fa-map"></i> Kawasan Hutan
        </a>
        <a href="<?= BASE_URL ?>pages/admin/kelola_polisi.php" class="nav-item <?= $currentPage === 'kelola_polisi.php' ? 'active' : '' ?>">
            <i class="fas fa-hard-hat"></i> Akun Polisi Hutan
        </a>

        <div class="nav-section-title">Sistem</div>
        <a href="<?= BASE_URL ?>logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i> Keluar Sistem
        </a>
    </nav>

    <div style="padding:10px 14px;background:rgba(0,0,0,0.2);border-top:1px solid rgba(255,255,255,0.07);font-size:10px;color:rgba(255,255,255,0.3);text-align:center">
        SIKAWAS &copy; <?= date('Y') ?>
    </div>
</div>