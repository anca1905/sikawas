<?php
require_once '../../config/init.php';
requireLogin('admin');
$db = getDB();

// Stats
$total   = $db->query("SELECT COUNT(*) FROM pengaduan")->fetchColumn();
$baru    = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Baru'")->fetchColumn();
$proses  = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Diproses'")->fetchColumn();
$selesai = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Selesai'")->fetchColumn();
$diverif = $db->query("SELECT COUNT(*) FROM pengaduan WHERE status='Diverifikasi'")->fetchColumn();

// Recent
$recent = $db->query("SELECT p.*,m.nama as pelapor,j.nama_jenis,h.nama_hutan FROM pengaduan p JOIN masyarakat m ON p.id_user=m.id_user JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan ORDER BY p.tanggal_lapor DESC LIMIT 7")->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard Admin — SIKAWAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_admin.php'; ?>
        <div class="main-content">
            <div class="topbar">
    
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <div class="topbar-left">
                <div class="topbar-title">
                    <h1>Dashboard</h1>
                    <p>Selamat datang, <?= sanitize($_SESSION['nama']) ?> 👋</p>
                </div>
                <div class="topbar-actions">
                    <span style="font-size:12px;color:#64748b"><?= date('d M Y') ?></span>
                    <a href="<?= BASE_URL ?>pages/admin/laporan_baru.php" class="btn btn-primary btn-sm"><i class="fas fa-bell"></i> Laporan Baru <?php if ($baru > 0): ?><span style="background:rgba(255,255,255,.3);border-radius:20px;padding:1px 6px"><?= $baru ?></span><?php endif; ?></a>
                </div>
            </div>
            <div class="page-body">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $total ?></div>
                            <div class="stat-label">Total Laporan</div>
                        </div>
                    </div>
                    <div class="stat-card amber">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $baru ?></div>
                            <div class="stat-label">Laporan Baru</div>
                        </div>
                    </div>
                    <div class="stat-card teal">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $diverif ?></div>
                            <div class="stat-label">Diverifikasi</div>
                        </div>
                    </div>
                    <div class="stat-card purple">
                        <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $proses ?></div>
                            <div class="stat-label">Diproses</div>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $selesai ?></div>
                            <div class="stat-label">Selesai</div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie" style="color:#1a7a3f;margin-right:8px"></i>Proporsi Status Laporan</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($total > 0): ?>
                            <?php
                            $statuses = [
                                ['label' => 'Baru', 'val' => $baru, 'color' => '#f97316'],
                                ['label' => 'Diverifikasi', 'val' => $diverif, 'color' => '#3b82f6'],
                                ['label' => 'Diproses', 'val' => $proses, 'color' => '#eab308'],
                                ['label' => 'Selesai', 'val' => $selesai, 'color' => '#22c55e'],
                            ];
                            foreach ($statuses as $s):
                                $pct = $total > 0 ? round($s['val'] / $total * 100) : 0;
                            ?>
                                <div style="margin-bottom:14px">
                                    <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:500;margin-bottom:5px">
                                        <span><?= $s['label'] ?></span>
                                        <span style="color:#64748b"><?= $s['val'] ?> (<?= $pct ?>%)</span>
                                    </div>
                                    <div style="background:#f1f5f9;border-radius:20px;height:8px">
                                        <div style="width:<?= $pct ?>%;background:<?= $s['color'] ?>;height:8px;border-radius:20px;transition:width 1s ease"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align:center;color:#94a3b8;padding:20px 0">Belum ada data laporan.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list" style="color:#1a7a3f;margin-right:8px"></i>Laporan Terbaru</h3>
                        <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
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
                                        <th>Tanggal</th>
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
                                <h3>Belum ada laporan</h3>
                                <p>Semua laporan akan muncul di sini.</p>
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