<?php
require_once '../../config/init.php';
requireLogin('polisi');
$db  = getDB();
$pid = $_SESSION['user_id'];

$total   = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_polisi=?");
$total->execute([$pid]);
$total = $total->fetchColumn();
$proses  = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_polisi=? AND status='Diproses'");
$proses->execute([$pid]);
$proses = $proses->fetchColumn();
$selesai = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_polisi=? AND status='Selesai'");
$selesai->execute([$pid]);
$selesai = $selesai->fetchColumn();
$baru    = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_polisi=? AND status='Diverifikasi'");
$baru->execute([$pid]);
$baru = $baru->fetchColumn();

$recent = $db->prepare("SELECT p.*,m.nama as pelapor,j.nama_jenis,h.nama_hutan FROM pengaduan p JOIN masyarakat m ON p.id_user=m.id_user JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan WHERE p.id_polisi=? ORDER BY p.tanggal_lapor DESC LIMIT 5");
$recent->execute([$pid]);
$recent = $recent->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard — SIKAWAS | Polisi Hutan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_polisi.php'; ?>
        <div class="main-content">
            <div class="topbar-left">\s*<button class="hamburger"</button>
                <div class="topbar-title">
                    <h1>Dashboard</h1>
                    <p>Selamat datang, <?= sanitize($_SESSION['nama']) ?> 👋</p>
                </div>
            </div>
        </div>
            <div class="page-body">
                <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>
                <div style="background:linear-gradient(135deg,#1e40af,#3b82f6);border-radius:16px;padding:28px 32px;margin-bottom:24px;color:#fff;position:relative;overflow:hidden">
                    <div style="position:absolute;right:-30px;top:-30px;font-size:120px;opacity:.08">🛡️</div>
                    <h2 style="font-size:20px;font-weight:800;margin-bottom:6px">Halo, <?= sanitize($_SESSION['nama']) ?>!</h2>
                    <p style="opacity:.8;font-size:14px">Anda memiliki <strong><?= $baru ?></strong> pengaduan baru yang menunggu ditindaklanjuti.</p>
                </div>
                <div class="stats-grid">
                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fas fa-list-ul"></i></div>
                        <div class="stat-value"><?= $total ?></div>
                        <div class="stat-label">Total Ditugaskan</div>
                    </div>
                    <div class="stat-card amber">
                        <div class="stat-icon"><i class="fas fa-bell"></i></div>
                        <div class="stat-value"><?= $baru ?></div>
                        <div class="stat-label">Baru Masuk</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                        <div class="stat-value"><?= $proses ?></div>
                        <div class="stat-label">Sedang Diproses</div>
                    </div>
                    <div class="stat-card emerald">
                        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                        <div class="stat-value"><?= $selesai ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list-ul" style="color:#1a7a3f;margin-right:8px"></i>Pengaduan Terbaru</h3><a href="daftar_pengaduan.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <?php if (count($recent) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Pelapor</th>
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
                                            <td><strong><?= sanitize($r['pelapor']) ?></strong></td>
                                            <td><?= sanitize($r['nama_jenis']) ?></td>
                                            <td><?= sanitize($r['nama_hutan']) ?></td>
                                            <td><?= sanitize($r['lokasi']) ?></td>
                                            <td><?= formatTanggal($r['tanggal_lapor']) ?></td>
                                            <td><?= getStatusBadge($r['status']) ?></td>
                                            <td><a href="detail_pengaduan.php?id=<?= $r['id_laporan'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                                <h3>Belum ada pengaduan</h3>
                                <p>Belum ada pengaduan yang ditugaskan kepada Anda.</p>
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