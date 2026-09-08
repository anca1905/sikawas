<?php
require_once '../../config/init.php';
requireLogin('polisi');
$db  = getDB();
$pid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . 'pages/polisi/daftar_pengaduan.php');

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status  = sanitize($_POST['status'] ?? '');
    $catatan = sanitize($_POST['catatan_polisi'] ?? '');
    if (in_array($status, ['Diproses', 'Selesai'])) {
        $stmt = $db->prepare("UPDATE pengaduan SET status=?,catatan_polisi=? WHERE id_laporan=? AND id_polisi=?");
        $stmt->execute([$status, $catatan, $id, $pid]);
        setFlash('success', "Status laporan berhasil diperbarui menjadi \"$status\".");
    }
    redirect(BASE_URL . "pages/polisi/detail_pengaduan.php?id=$id");
}

$stmt = $db->prepare("SELECT p.*,m.nama as pelapor,m.no_hp,j.nama_jenis,h.nama_hutan,h.lokasi_hutan FROM pengaduan p JOIN masyarakat m ON p.id_user=m.id_user JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan WHERE p.id_laporan=? AND p.id_polisi=?");
$stmt->execute([$id, $pid]);
$r = $stmt->fetch();
if (!$r) redirect(BASE_URL . 'pages/polisi/daftar_pengaduan.php');
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Detail Pengaduan — SIKAWAS | Polisi Hutan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .info-row {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9
        }

        .info-row:last-child {
            border-bottom: none
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            min-width: 130px
        }

        .info-val {
            font-size: 13px;
            color: #1e293b;
            font-weight: 500
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_polisi.php'; ?>
        <div class="main-content">
            <div class="topbar-left">\s*<button class="hamburger"</button>
                <div class="topbar-title">
                    <h1>Detail & Tindak Lanjut</h1>
                    <p>Laporan #<?= $id ?></p>
                </div>
                <div class="topbar-actions"><a href="daftar_pengaduan.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="page-body">
                <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                    <div>
                        <div class="card" style="margin-bottom:20px">
                            <div class="card-header">
                                <h3><i class="fas fa-file-alt" style="color:#1a7a3f;margin-right:8px"></i>Info Laporan</h3><?= getStatusBadge($r['status']) ?>
                            </div>
                            <div class="card-body">
                                <div class="info-row"><span class="info-label">Jenis Kejahatan</span><span class="info-val"><?= sanitize($r['nama_jenis']) ?></span></div>
                                <div class="info-row"><span class="info-label">Kawasan Hutan</span><span class="info-val"><?= sanitize($r['nama_hutan']) ?></span></div>
                                <div class="info-row"><span class="info-label">Lokasi</span><span class="info-val"><?= sanitize($r['lokasi']) ?></span></div>
                                <div class="info-row"><span class="info-label">Tgl Kejadian</span><span class="info-val"><?= formatTanggal($r['tanggal_kejadian']) ?></span></div>
                                <div class="info-row"><span class="info-label">Pelapor</span><span class="info-val"><?= sanitize($r['pelapor']) ?> <?= $r['no_hp'] ? '(' . $r['no_hp'] . ')' : '' ?></span></div>
                                <div class="info-row" style="flex-direction:column;gap:6px"><span class="info-label">Deskripsi</span><span class="info-val" style="background:#f8fafc;padding:12px;border-radius:8px;line-height:1.7"><?= nl2br(sanitize($r['deskripsi'])) ?></span></div>
                                <?php if ($r['catatan_admin']): ?>
                                    <div class="info-row" style="flex-direction:column;gap:6px"><span class="info-label">Catatan Admin</span><span class="info-val" style="background:#f0fdf4;padding:12px;border-radius:8px;color:#15803d"><?= nl2br(sanitize($r['catatan_admin'])) ?></span></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($r['bukti_foto']): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-camera" style="color:#1a7a3f;margin-right:8px"></i>Bukti Foto</h3>
                                </div>
                                <div class="card-body"><img src="<?= BASE_URL ?><?= sanitize($r['bukti_foto']) ?>" style="width:100%;border-radius:10px;border:2px solid #e2e8f0;max-height:260px;object-fit:cover;cursor:zoom-in" onclick="window.open('<?= BASE_URL ?><?= sanitize($r['bukti_foto']) ?>','_blank')"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tindak Lanjut Panel -->
                    <div>
                        <?php if ($r['status'] === 'Selesai'): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-check-double" style="color:#22c55e;margin-right:8px"></i>Laporan Selesai</h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Laporan ini sudah selesai ditangani.</div>
                                    <?php if ($r['catatan_polisi']): ?>
                                        <div style="background:#eff6ff;padding:14px;border-radius:10px;border-left:4px solid #3b82f6;margin-top:12px">
                                            <p style="font-size:12px;font-weight:600;color:#1d4ed8;margin-bottom:4px"><i class="fas fa-hard-hat"></i> Catatan penanganan:</p>
                                            <p style="font-size:13px"><?= nl2br(sanitize($r['catatan_polisi'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-tools" style="color:#f59e0b;margin-right:8px"></i>Update Status Penanganan</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label class="form-label">Status Penanganan <span style="color:red">*</span></label>
                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:4px">
                                                <label style="display:flex;align-items:center;gap:10px;padding:14px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;transition:all .3s" onclick="this.style.borderColor='#eab308';document.querySelector('.status-opt-s').style.borderColor='#e2e8f0'">
                                                    <input type="radio" name="status" value="Diproses" <?= $r['status'] === 'Diproses' ? 'checked' : '' ?> style="accent-color:#eab308" class="status-opt-p" required>
                                                    <div>
                                                        <p style="font-size:13px;font-weight:600;color:#92400e">Sedang Diproses</p>
                                                        <p style="font-size:11px;color:#94a3b8">Sedang ditangani di lapangan</p>
                                                    </div>
                                                </label>
                                                <label style="display:flex;align-items:center;gap:10px;padding:14px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;transition:all .3s" onclick="this.style.borderColor='#22c55e';document.querySelector('.status-opt-p').style.borderColor='#e2e8f0'" class="status-opt-s">
                                                    <input type="radio" name="status" value="Selesai" <?= $r['status'] === 'Selesai' ? 'checked' : '' ?> style="accent-color:#22c55e">
                                                    <div>
                                                        <p style="font-size:13px;font-weight:600;color:#15803d">Selesai</p>
                                                        <p style="font-size:11px;color:#94a3b8">Kasus telah diselesaikan</p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Catatan Penanganan</label>
                                            <textarea name="catatan_polisi" class="form-control" rows="5" placeholder="Tulis catatan penanganan di lapangan, tindakan yang diambil, hasil investigasi, dll..."><?= sanitize($r['catatan_polisi'] ?? '') ?></textarea>
                                            <div class="form-hint">Catatan akan terlihat oleh Admin dan Masyarakat pelapor.</div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" style="width:100%"><i class="fas fa-save"></i> Simpan Update Status</button>
                                    </form>
                                </div>
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