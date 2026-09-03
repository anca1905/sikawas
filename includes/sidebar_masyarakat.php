<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$nama = $_SESSION['nama'] ?? 'Pengguna';
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
            <div class="user-role"><i class="fas fa-users"></i> Masyarakat</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu</div>
        <a href="<?= BASE_URL ?>pages/masyarakat/dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Beranda
        </a>
        <a href="<?= BASE_URL ?>pages/masyarakat/buat_laporan.php" class="nav-item <?= $currentPage === 'buat_laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Buat Laporan Baru
        </a>
        <a href="<?= BASE_URL ?>pages/masyarakat/riwayat_laporan.php" class="nav-item <?= $currentPage === 'riwayat_laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-history"></i> Riwayat Laporan
        </a>
        <a href="<?= BASE_URL ?>pages/masyarakat/profil.php" class="nav-item <?= $currentPage === 'profil.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i> Profil Saya
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