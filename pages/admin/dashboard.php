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
                <div class="topbar-left">
                    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div class="topbar-title">
                        <h1>Dashboard</h1>
                        <p>Selamat datang, <?= sanitize($_SESSION['nama']) ?> 👋</p>
                    </div>
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

                <!-- Stats (Bisa diklik untuk filter laporan) -->
                <div class="stats-grid">
                    <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php" class="stat-card blue" title="Klik untuk lihat semua laporan">
                        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $total ?></div>
                            <div class="stat-label">Total Laporan</div>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php?status=Baru" class="stat-card amber" title="Klik untuk filter laporan baru">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $baru ?></div>
                            <div class="stat-label">Laporan Baru</div>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php?status=Diverifikasi" class="stat-card teal" title="Klik untuk filter laporan diverifikasi">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $diverif ?></div>
                            <div class="stat-label">Diverifikasi</div>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php?status=Diproses" class="stat-card purple" title="Klik untuk filter laporan diproses">
                        <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $proses ?></div>
                            <div class="stat-label">Diproses</div>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php?status=Selesai" class="stat-card green" title="Klik untuk filter laporan selesai">
                        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                        <div class="stat-text">
                            <div class="stat-value"><?= $selesai ?></div>
                            <div class="stat-label">Selesai</div>
                        </div>
                    </a>
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
                                ['label' => 'Baru', 'val' => $baru, 'color' => '#f97316', 'link' => 'Baru'],
                                ['label' => 'Diverifikasi', 'val' => $diverif, 'color' => '#3b82f6', 'link' => 'Diverifikasi'],
                                ['label' => 'Diproses', 'val' => $proses, 'color' => '#eab308', 'link' => 'Diproses'],
                                ['label' => 'Selesai', 'val' => $selesai, 'color' => '#22c55e', 'link' => 'Selesai'],
                            ];
                            foreach ($statuses as $s):
                                $pct = $total > 0 ? round($s['val'] / $total * 100) : 0;
                            ?>
                                <a href="<?= BASE_URL ?>pages/admin/kelola_laporan.php?status=<?= $s['link'] ?>" style="display:block;text-decoration:none;color:inherit;margin-bottom:14px">
                                    <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:500;margin-bottom:5px">
                                        <span><?= $s['label'] ?></span>
                                        <span style="color:#64748b"><?= $s['val'] ?> (<?= $pct ?>%)</span>
                                    </div>
                                    <div style="background:#f1f5f9;border-radius:20px;height:8px">
                                        <div style="width:<?= $pct ?>%;background:<?= $s['color'] ?>;height:8px;border-radius:20px;transition:width 1s ease"></div>
                                    </div>
                                </a>
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
                                            <td><a href="detail_pengaduan.php?id=<?= $r['id_laporan'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detail</a></td>
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