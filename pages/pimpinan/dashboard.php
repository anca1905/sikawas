<?php
require_once '../../config/init.php';
requireLogin('pimpinan');
$db = getDB();

$total   = $db->query("SELECT COUNT(*) FROM pengaduan")->fetchColumn();
$baru    = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Baru'")->fetchColumn();
$proses  = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Diproses'")->fetchColumn();
$selesai = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Selesai'")->fetchColumn();
$diverif = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Diverifikasi'")->fetchColumn();

// By jenis
$byJenis = $db->query("SELECT j.nama_jenis, COUNT(*) as jumlah FROM pengaduan p JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis GROUP BY j.id_jenis ORDER BY jumlah DESC LIMIT 5")->fetchAll();
// By kawasan
$byKawasan = $db->query("SELECT h.nama_hutan, COUNT(*) as jumlah FROM pengaduan p JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan GROUP BY h.id_hutan ORDER BY jumlah DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard — SIKAWAS | Pimpinan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_pimpinan.php'; ?>
        <div class="main-content">
            <div class="topbar">
    
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <div class="topbar-left">
                <div class="topbar-title">
                    <h1>Dashboard Pimpinan</h1>
                    <p>Ringkasan penanganan laporan kejahatan lingkungan</p>
                </div>
                <div class="topbar-actions">
                    <a href="rekap_laporan.php" class="btn btn-primary"><i class="fas fa-chart-bar"></i> Lihat Rekap Lengkap</a>
                </div>
            </div>
            <div class="page-body">
                <div style="background:linear-gradient(135deg,#7c3aed,#a855f7);border-radius:16px;padding:28px 32px;margin-bottom:24px;color:#fff;position:relative;overflow:hidden">
                    <div style="position:absolute;right:-30px;top:-30px;font-size:120px;opacity:.08">👔</div>
                    <h2 style="font-size:20px;font-weight:800;margin-bottom:6px">Halo, <?= sanitize($_SESSION['nama']) ?>!</h2>
                    <p style="opacity:.8;font-size:14px">Berikut adalah ringkasan data laporan kejahatan lingkungan di kawasan hutan.</p>
                </div>
                <div class="stats-grid">
                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="stat-value"><?= $total ?></div>
                        <div class="stat-label">Total Semua Laporan</div>
                    </div>
                    <div class="stat-card amber">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value"><?= $baru ?></div>
                        <div class="stat-label">Laporan Baru</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value"><?= $diverif ?></div>
                        <div class="stat-label">Diverifikasi</div>
                    </div>
                    <div class="stat-card green" style="--primary:#8b5cf6">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa)"><i class="fas fa-spinner"></i></div>
                        <div class="stat-value"><?= $proses ?></div>
                        <div class="stat-label">Diproses</div>
                    </div>
                    <div class="stat-card emerald">
                        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                        <div class="stat-value"><?= $selesai ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                    <!-- By Jenis -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-tags" style="color:#1a7a3f;margin-right:8px"></i>Laporan per Jenis Kejahatan</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($total > 0): foreach ($byJenis as $bj): $pct = round($bj['jumlah'] / $total * 100); ?>
                                    <div style="margin-bottom:14px">
                                        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:500;margin-bottom:5px"><span><?= sanitize($bj['nama_jenis']) ?></span><span style="color:#64748b"><?= $bj['jumlah'] ?> (<?= $pct ?>%)</span></div>
                                        <div style="background:#f1f5f9;border-radius:20px;height:8px">
                                            <div style="width:<?= $pct ?>%;background:linear-gradient(90deg,#1a7a3f,#2ea055);height:8px;border-radius:20px"></div>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else: ?><p style="text-align:center;color:#94a3b8;padding:20px">Belum ada data</p><?php endif; ?>
                        </div>
                    </div>

                    <!-- By Kawasan -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-map" style="color:#1a7a3f;margin-right:8px"></i>Laporan per Kawasan Hutan</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($total > 0): foreach ($byKawasan as $bk): $pct = round($bk['jumlah'] / $total * 100); ?>
                                    <div style="margin-bottom:14px">
                                        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:500;margin-bottom:5px"><span><?= sanitize($bk['nama_hutan']) ?></span><span style="color:#64748b"><?= $bk['jumlah'] ?> (<?= $pct ?>%)</span></div>
                                        <div style="background:#f1f5f9;border-radius:20px;height:8px">
                                            <div style="width:<?= $pct ?>%;background:linear-gradient(90deg,#f59e0b,#fbbf24);height:8px;border-radius:20px"></div>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else: ?><p style="text-align:center;color:#94a3b8;padding:20px">Belum ada data</p><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>