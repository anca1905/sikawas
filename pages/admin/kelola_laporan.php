<?php
require_once '../../config/init.php';
requireLogin('admin');
$db = getDB();

// Filter
$search  = sanitize($_GET['search'] ?? '');
$filterS = sanitize($_GET['status'] ?? '');
$filterJ = (int)($_GET['id_jenis'] ?? 0);

$sql = "SELECT p.*,m.nama as pelapor,j.nama_jenis,h.nama_hutan,ph.nama as nama_polisi FROM pengaduan p
        JOIN masyarakat m ON p.id_user=m.id_user
        JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis
        JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan
        LEFT JOIN polisi_hutan ph ON p.id_polisi=ph.id_polisi
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (m.nama LIKE ? OR p.lokasi LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%"]);
}
if ($filterS) {
    $sql .= " AND p.status=?";
    $params[] = $filterS;
}
if ($filterJ) {
    $sql .= " AND p.id_jenis=?";
    $params[] = $filterJ;
}
$sql .= " ORDER BY p.tanggal_lapor DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();
$jenisList = $db->query("SELECT * FROM jenis_kejahatan ORDER BY nama_jenis")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Kelola Laporan — SIKAWAS</title>
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
                    <h1>Kelola Laporan</h1>
                    <p>Semua laporan kejahatan lingkungan</p>
                </div>
            </div>
            <div class="page-body">
                <form method="GET" class="filter-bar">
                    <div class="form-group">
                        <label class="form-label">Cari</label>
                        <div class="input-group"><i class="fas fa-search input-icon"></i>
                            <input type="text" name="search" class="form-control" placeholder="Nama/Lokasi..." value="<?= $search ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" style="width:160px">
                            <option value="">Semua Status</option>
                            <?php foreach (['Baru', 'Diverifikasi', 'Diproses', 'Selesai', 'Ditolak'] as $s): ?>
                                <option value="<?= $s ?>" <?= $filterS === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kejahatan</label>
                        <select name="id_jenis" class="form-select" style="width:180px">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($jenisList as $j): ?>
                                <option value="<?= $j['id_jenis'] ?>" <?= $filterJ == $j['id_jenis'] ? 'selected' : '' ?>><?= sanitize($j['nama_jenis']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="kelola_laporan.php" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
                </form>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-folder-open" style="color:#1a7a3f;margin-right:8px"></i>Daftar Laporan
                            <span style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-size:11px;padding:3px 10px;border-radius:20px;margin-left:8px"><?= count($laporan) ?> laporan</span>
                        </h3>
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
                                        <th>Tanggal</th>
                                        <th>Polisi</th>
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
                                            <td><span style="max-width:140px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= sanitize($r['lokasi']) ?></span></td>
                                            <td><?= formatTanggal($r['tanggal_kejadian']) ?></td>
                                            <td><?= $r['nama_polisi'] ? sanitize($r['nama_polisi']) : '<span style="color:#94a3b8">-</span>' ?></td>
                                            <td><?= getStatusBadge($r['status']) ?></td>
                                            <td><a href="detail_pengaduan.php?id=<?= $r['id_laporan'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detail</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                                <h3>Tidak ada laporan ditemukan</h3>
                                <p>Coba ubah filter pencarian Anda.</p>
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