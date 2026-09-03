<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$nama = $_SESSION['nama'] ?? 'Pimpinan';
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
        <div class="user-avatar" style="background:#6C3483"><?= strtoupper(substr($nama, 0, 1)) ?></div>
        <div class="user-info">
            <div class="user-name"><?= sanitize($nama) ?></div>
            <div class="user-role"><i class="fas fa-user-tie"></i> Pimpinan</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu</div>
        <a href="<?= BASE_URL ?>pages/pimpinan/dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Beranda Dashboard
        </a>
        <a href="<?= BASE_URL ?>pages/pimpinan/rekap_laporan.php" class="nav-item <?= $currentPage === 'rekap_laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Rekap Laporan
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