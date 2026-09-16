<?php
require_once '../../config/init.php';
requireLogin('polisi');
$db  = getDB();
$pid = $_SESSION['user_id'];

$filterS = sanitize($_GET['status'] ?? '');
$sql = "SELECT p.*,m.nama as pelapor,j.nama_jenis,h.nama_hutan FROM pengaduan p JOIN masyarakat m ON p.id_user=m.id_user JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan WHERE p.id_polisi=?";
$params = [$pid];
if ($filterS) {
    $sql .= " AND p.status=?";
    $params[] = $filterS;
}
$sql .= " ORDER BY p.tanggal_lapor DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Daftar Pengaduan — SIKAWAS | Polisi Hutan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_polisi.php'; ?>
        <div class="main-content">
            <div class="topbar">
    <div class="topbar-left">
        <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <div class="topbar-title">
            <h1>Daftar Pengaduan</h1>
                    <p>Pengaduan yang ditugaskan kepada Anda</p>
        </div>
    </div>
</div>
<div class="page-body">
                <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>
                <form method="GET" class="filter-bar">
                    <div class="form-group"><label class="form-label">Filter Status</label>
                        <select name="status" class="form-select" style="width:170px" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <?php foreach (['Diverifikasi', 'Diproses', 'Selesai'] as $s): ?>
                                <option value="<?= $s ?>" <?= $filterS === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="daftar_pengaduan.php" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
                </form>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list-ul" style="color:#1a7a3f;margin-right:8px"></i>Pengaduan Saya (<?= count($laporan) ?>)</h3>
                    </div>
                    <div class="table-responsive">
                        <?php if (count($laporan) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Pelapor</th>
                                        <th>Jenis Kejahatan</th>
                                        <th>Kawasan</th>
                                        <th>Lokasi</th>
                                        <th>Tgl Kejadian</th>
                                        <th>Tgl Lapor</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= sanitize($r['pelapor']) ?></strong></td>
                                            <td><?= sanitize($r['nama_jenis']) ?></td>
                                            <td><?= sanitize($r['nama_hutan']) ?></td>
                                            <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= sanitize($r['lokasi']) ?></td>
                                            <td><?= formatTanggal($r['tanggal_kejadian']) ?></td>
                                            <td><?= formatTanggal($r['tanggal_lapor']) ?></td>
                                            <td><?= getStatusBadge($r['status']) ?></td>
                                            <td><a href="detail_pengaduan.php?id=<?= $r['id_laporan'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Tindak Lanjut</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                                <h3>Tidak ada pengaduan</h3>
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