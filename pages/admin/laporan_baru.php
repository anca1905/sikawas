<?php
require_once '../../config/init.php';
requireLogin('admin');
$db = getDB();

// Handle Hapus Laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'hapus') {
    $idHapus = (int)($_POST['id_laporan'] ?? 0);
    if ($idHapus && hapusLaporan($idHapus)) {
        setFlash('success', "Laporan #$idHapus berhasil dihapus.");
    } else {
        setFlash('danger', "Gagal menghapus laporan.");
    }
    redirect(BASE_URL . 'pages/admin/laporan_baru.php');
}

$flash = getFlash();

$laporan = $db->query("SELECT p.*,m.nama as pelapor,j.nama_jenis,h.nama_hutan FROM pengaduan p
    JOIN masyarakat m ON p.id_user=m.id_user
    JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis
    JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan
    WHERE p.status='Baru' ORDER BY p.tanggal_lapor DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Laporan Baru — SIKAWAS</title>
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
            <h1>Laporan Baru</h1>
                    <p>Daftar laporan yang belum diverifikasi</p>
        </div>
    </div>
    <div class="topbar-actions">
        <a href="verifikasi_laporan.php" class="btn btn-primary btn-sm"><i class="fas fa-clipboard-check"></i> Ke Halaman Verifikasi</a>
    </div>
</div>
<div class="page-body">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bell" style="color:#ef4444;margin-right:8px"></i>Daftar Laporan Baru
                            <?php if (count($laporan) > 0): ?>
                                <span style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:11px;padding:3px 10px;border-radius:20px;margin-left:8px"><?= count($laporan) ?> belum diverifikasi</span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <?php if (count($laporan) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Pelapor</th>
                                        <th>Tgl Masuk</th>
                                        <th>Jenis Kejahatan</th>
                                        <th>Kawasan</th>
                                        <th>Lokasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= sanitize($r['pelapor']) ?></strong></td>
                                            <td><?= formatTanggal($r['tanggal_lapor']) ?></td>
                                            <td><span class="badge badge-new"><?= sanitize($r['nama_jenis']) ?></span></td>
                                            <td><?= sanitize($r['nama_hutan']) ?></td>
                                            <td><?= sanitize($r['lokasi']) ?></td>
                                            <td>
                                                <div style="display:flex;gap:6px">
                                                    <a href="detail_pengaduan.php?id=<?= $r['id_laporan'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Kelola</a>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteLaporan(<?= $r['id_laporan'] ?>)"><i class="fas fa-trash-alt"></i> Hapus</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-check-circle"></i></div>
                                <h3>Tidak ada laporan baru</h3>
                                <p>Semua laporan sudah diverifikasi. Kerja bagus!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" id="formHapusLaporan" style="display:none">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id_laporan" id="inputHapusId">
    </form>
    <script>
        function confirmDeleteLaporan(id) {
            if (confirm('Apakah Anda yakin ingin menghapus laporan #' + id + ' ini?\nSemua data laporan dan bukti foto akan dihapus secara permanen.')) {
                document.getElementById('inputHapusId').value = id;
                document.getElementById('formHapusLaporan').submit();
            }
        }
    </script>

<?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>