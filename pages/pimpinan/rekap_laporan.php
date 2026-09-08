<?php
require_once '../../config/init.php';
requireLogin('pimpinan');
$db = getDB();

$tglMulai = sanitize($_GET['tgl_mulai'] ?? '');
$tglAkhir = sanitize($_GET['tgl_akhir'] ?? '');
$filterS  = sanitize($_GET['status'] ?? '');
$filterH  = (int)($_GET['id_hutan'] ?? 0);
$filterJ  = (int)($_GET['id_jenis'] ?? 0);

$sql = "SELECT p.*,m.nama as pelapor,j.nama_jenis,h.nama_hutan,ph.nama as nama_polisi FROM pengaduan p JOIN masyarakat m ON p.id_user=m.id_user JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan LEFT JOIN polisi_hutan ph ON p.id_polisi=ph.id_polisi WHERE 1=1";
$params = [];
if ($tglMulai) {
    $sql .= " AND DATE(p.tanggal_lapor)>=?";
    $params[] = $tglMulai;
}
if ($tglAkhir) {
    $sql .= " AND DATE(p.tanggal_lapor)<=?";
    $params[] = $tglAkhir;
}
if ($filterS) {
    $sql .= " AND p.status=?";
    $params[] = $filterS;
}
if ($filterH) {
    $sql .= " AND p.id_hutan=?";
    $params[] = $filterH;
}
if ($filterJ) {
    $sql .= " AND p.id_jenis=?";
    $params[] = $filterJ;
}
$sql .= " ORDER BY p.tanggal_lapor DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();

$hutanList = $db->query("SELECT * FROM jenis_kawasan_hutan ORDER BY nama_hutan")->fetchAll();
$jenisList = $db->query("SELECT * FROM jenis_kejahatan ORDER BY nama_jenis")->fetchAll();
$sTotal = count($laporan);
$sMap = ['Baru' => 0, 'Diverifikasi' => 0, 'Diproses' => 0, 'Selesai' => 0, 'Ditolak' => 0];
foreach ($laporan as $r) if (isset($sMap[$r['status']])) $sMap[$r['status']]++;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rekap Laporan — SIKAWAS | Pimpinan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        @media print {

            .sidebar,
            .topbar,
            .no-print {
                display: none !important
            }

            .main-content {
                margin-left: 0 !important
            }

            body {
                background: #fff
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd
            }

            .print-kop {
                display: block !important
            }
        }

        .print-kop {
            display: none;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a7a3f;
            padding-bottom: 12px
        }

        .print-kop h1 {
            font-size: 16px;
            font-weight: 800
        }

        .print-kop p {
            font-size: 12px;
            color: #64748b
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_pimpinan.php'; ?>
        <div class="main-content">
            <div class="topbar-left">\s*<button class="hamburger"</button>
                <div class="topbar-title">
                    <h1>Rekap Laporan</h1>
                    <p>Rekapitulasi & analisis data laporan</p>
                </div>
                <div class="topbar-actions">
                    <button onclick="window.print()" class="btn btn-primary no-print"><i class="fas fa-print"></i> Cetak Laporan</button>
                </div>
            </div>
            <div class="page-body">
                <!-- Print Kop -->
                <div class="print-kop">
                    <h1>🌿 REKAP LAPORAN KEJAHATAN LINGKUNGAN DI KAWASAN HUTAN</h1>
                    <p>Sistem Informasi Pelaporan Kejahatan Lingkungan (SIKAWAS)</p>
                    <p>Dicetak: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; Total: <?= $sTotal ?> laporan</p>
                </div>

                <form method="GET" class="filter-bar no-print">
                    <div class="form-group"><label class="form-label">Tgl Mulai</label><input type="date" name="tgl_mulai" class="form-control" style="width:155px" value="<?= $tglMulai ?>"></div>
                    <div class="form-group"><label class="form-label">Tgl Akhir</label><input type="date" name="tgl_akhir" class="form-control" style="width:155px" value="<?= $tglAkhir ?>"></div>
                    <div class="form-group"><label class="form-label">Status</label>
                        <select name="status" class="form-select" style="width:150px">
                            <option value="">Semua Status</option>
                            <?php foreach (['Baru', 'Diverifikasi', 'Diproses', 'Selesai', 'Ditolak'] as $s): ?><option value="<?= $s ?>" <?= $filterS === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Kawasan</label>
                        <select name="id_hutan" class="form-select" style="min-width:190px">
                            <option value="">Semua Kawasan</option>
                            <?php foreach ($hutanList as $h): ?><option value="<?= $h['id_hutan'] ?>" <?= $filterH == $h['id_hutan'] ? 'selected' : '' ?>><?= sanitize($h['nama_hutan']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Jenis Kejahatan</label>
                        <select name="id_jenis" class="form-select" style="min-width:175px">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($jenisList as $j): ?><option value="<?= $j['id_jenis'] ?>" <?= $filterJ == $j['id_jenis'] ? 'selected' : '' ?>><?= sanitize($j['nama_jenis']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="rekap_laporan.php" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="stats-grid" style="grid-template-columns:repeat(5,1fr)">
                    <div class="stat-card amber">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value"><?= $sMap['Baru'] ?></div>
                        <div class="stat-label">Baru</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value"><?= $sMap['Diverifikasi'] ?></div>
                        <div class="stat-label">Diverifikasi</div>
                    </div>
                    <div class="stat-card green" style="--primary:#8b5cf6">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa)"><i class="fas fa-spinner"></i></div>
                        <div class="stat-value"><?= $sMap['Diproses'] ?></div>
                        <div class="stat-label">Diproses</div>
                    </div>
                    <div class="stat-card emerald">
                        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                        <div class="stat-value"><?= $sMap['Selesai'] ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                        <div class="stat-value"><?= $sMap['Ditolak'] ?></div>
                        <div class="stat-label">Ditolak</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar" style="color:#1a7a3f;margin-right:8px"></i>Data Rekap Laporan (<?= $sTotal ?> laporan)</h3>
                    </div>
                    <div class="table-responsive">
                        <?php if ($sTotal > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Pelapor</th>
                                        <th>Jenis Kejahatan</th>
                                        <th>Kawasan Hutan</th>
                                        <th>Lokasi</th>
                                        <th>Tgl Kejadian</th>
                                        <th>Tgl Lapor</th>
                                        <th>Polisi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= sanitize($r['pelapor']) ?></strong></td>
                                            <td><?= sanitize($r['nama_jenis']) ?></td>
                                            <td><?= sanitize($r['nama_hutan']) ?></td>
                                            <td><?= sanitize($r['lokasi']) ?></td>
                                            <td><?= formatTanggal($r['tanggal_kejadian']) ?></td>
                                            <td><?= formatTanggal($r['tanggal_lapor']) ?></td>
                                            <td><?= $r['nama_polisi'] ? sanitize($r['nama_polisi']) : '<span style="color:#94a3b8">-</span>' ?></td>
                                            <td><?= getStatusBadge($r['status']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-search"></i></div>
                                <h3>Data tidak ditemukan</h3>
                                <p>Coba ubah parameter filter.</p>
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