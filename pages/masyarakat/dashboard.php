<?php
require_once '../../config/init.php';
requireLogin('masyarakat');
$db = getDB();
$uid = $_SESSION['user_id'];

$total   = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_user=?");
$total->execute([$uid]);
$total   = $total->fetchColumn();
$baru    = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_user=? AND status='Baru'");
$baru->execute([$uid]);
$baru = $baru->fetchColumn();
$proses  = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_user=? AND status IN ('Diverifikasi','Diproses')");
$proses->execute([$uid]);
$proses = $proses->fetchColumn();
$selesai = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_user=? AND status='Selesai'");
$selesai->execute([$uid]);
$selesai = $selesai->fetchColumn();

$recent = $db->prepare("SELECT p.*,j.nama_jenis,h.nama_hutan FROM pengaduan p JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan WHERE p.id_user=? ORDER BY p.tanggal_lapor DESC LIMIT 5");
$recent->execute([$uid]);
$recent = $recent->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard — SIKAWAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_masyarakat.php'; ?>
        <div class="main-content">
            <div class="topbar-left">\s*<button class="hamburger"</button>
                    <div class="topbar-title">
                        <h1>Dashboard</h1>
                        <p>Selamat datang, <?= sanitize($_SESSION['nama']) ?> 👋</p>
                    </div>
                </div>
                <div class="topbar-actions">
                    <a href="buat_laporan.php" class="btn btn-primary"><i class="fas fa-plus"></i> <span>Buat Laporan</span></a>
                </div>
            </div>

            <div class="page-body">
                <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>

                <!-- Hero Card -->
                <div style="background:linear-gradient(135deg,#1a7a3f,#2ea055);border-radius:12px;padding:24px 28px;margin-bottom:20px;color:#fff;position:relative;overflow:hidden">
                    <div style="position:absolute;right:-20px;top:-20px;font-size:100px;opacity:.07">🌳</div>
                    <h2 style="font-size:18px;font-weight:800;margin-bottom:5px">Halo, <?= sanitize($_SESSION['nama']) ?>!</h2>
                    <p style="opacity:.8;font-size:13px;margin-bottom:16px">Laporkan kejahatan lingkungan di kawasan hutan untuk menjaga kelestarian alam kita.</p>
                    <a href="buat_laporan.php" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.2);border:1.5px solid rgba(255,255,255,.4);color:#fff;text-decoration:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;backdrop-filter:blur(10px);transition:all .3s" onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                        <i class="fas fa-plus-circle"></i> Buat Laporan Baru
                    </a>
                </div>

                <div class="stats-grid stats-grid-4">
                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $total ?></div>
                            <div class="stat-label">Total Laporan Saya</div>
                        </div>
                    </div>
                    <div class="stat-card amber">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $baru ?></div>
                            <div class="stat-label">Menunggu Verifikasi</div>
                        </div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $proses ?></div>
                            <div class="stat-label">Sedang Diproses</div>
                        </div>
                    </div>
                    <div class="stat-card emerald">
                        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $selesai ?></div>
                            <div class="stat-label">Selesai Ditangani</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history" style="color:#1a7a3f;margin-right:8px"></i>Laporan Terbaru Saya</h3>
                        <a href="riwayat_laporan.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <?php if (count($recent) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jenis Kejahatan</th>
                                        <th>Kawasan</th>
                                        <th>Lokasi</th>
                                        <th>Tgl Lapor</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= sanitize($r['nama_jenis']) ?></td>
                                            <td><?= sanitize($r['nama_hutan']) ?></td>
                                            <td><?= sanitize($r['lokasi']) ?></td>
                                            <td><?= formatTanggal($r['tanggal_lapor']) ?></td>
                                            <td><?= getStatusBadge($r['status']) ?></td>
                                            <td><a href="detail_laporan.php?id=<?= $r['id_laporan'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-file-plus"></i></div>
                                <h3>Belum ada laporan</h3>
                                <p>Mulai buat laporan pertama Anda!</p>
                                <a href="buat_laporan.php" class="btn btn-primary" style="margin-top:12px"><i class="fas fa-plus"></i> Buat Laporan</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>