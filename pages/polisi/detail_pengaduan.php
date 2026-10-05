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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .info-row {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            min-width: 130px;
        }

        .info-val {
            font-size: 13px;
            color: #1e293b;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_polisi.php'; ?>
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-left">
                    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div class="topbar-title">
                        <h1>Detail & Tindak Lanjut</h1>
                        <p>Laporan #<?= $id ?></p>
                    </div>
                </div>
                <div class="topbar-actions">
                    <a href="daftar_pengaduan.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="page-body">
                <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                    <!-- Kiri: Info Laporan & Bukti Foto -->
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

                        <!-- Peta Lokasi Kejadian (Maps) -->
                        <div class="card" style="margin-bottom:20px">
                            <div class="card-header">
                                <h3><i class="fas fa-map-marked-alt" style="color:#1a7a3f;margin-right:8px"></i>Peta Lokasi Kejadian</h3>
                                <?php if (!empty($r['latitude']) && !empty($r['longitude'])): ?>
                                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $r['latitude'] ?>,<?= $r['longitude'] ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size:11px">
                                        <i class="fas fa-directions"></i> Petunjuk Arah Google Maps
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body" style="padding:0">
                                <div id="mapPolisi" style="height:270px;width:100%;z-index:1"></div>
                                <div style="padding:12px 14px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;flex-direction:column;gap:6px">
                                    <div style="font-size:12px;color:#1e293b;display:flex;align-items:center;gap:6px">
                                        <i class="fas fa-map-pin" style="color:#ef4444"></i>
                                        <strong>Koordinat:</strong>
                                        <?php if (!empty($r['latitude']) && !empty($r['longitude'])): ?>
                                            <span style="font-family:monospace;background:#e2e8f0;padding:2px 8px;border-radius:4px;font-weight:600"><?= sanitize($r['latitude']) ?>, <?= sanitize($r['longitude']) ?></span>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;font-style:italic">Tidak ada koordinat GPS khusus</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size:11.5px;color:#64748b">
                                        <i class="fas fa-map-marker-alt" style="color:#1a7a3f;margin-right:4px"></i>
                                        <strong>Alamat:</strong> <?= sanitize($r['lokasi']) ?>
                                    </div>
                                </div>
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

                    <!-- Kanan: Tindak Lanjut Panel -->
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const reportLat = <?= !empty($r['latitude']) ? (float)$r['latitude'] : 'null' ?>;
        const reportLng = <?= !empty($r['longitude']) ? (float)$r['longitude'] : 'null' ?>;
        const lokasiText = <?= json_encode($r['lokasi']) ?>;
        const jenisText = <?= json_encode($r['nama_jenis']) ?>;

        const mapPolisi = L.map('mapPolisi');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(mapPolisi);

        if (reportLat !== null && reportLng !== null) {
            mapPolisi.setView([reportLat, reportLng], 14);
            const m = L.marker([reportLat, reportLng]).addTo(mapPolisi);
            m.bindPopup(`<b>TKP Laporan #${<?= (int)$r['id_laporan'] ?>}</b><br><b>Jenis:</b> ${jenisText}<br><b>Lokasi:</b> ${lokasiText}<br><b>Koordinat:</b> ${reportLat}, ${reportLng}`).openPopup();
        } else {
            mapPolisi.setView([-4.18, 121.60], 7);
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(lokasiText)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        mapPolisi.setView([lat, lon], 12);
                        L.marker([lat, lon]).addTo(mapPolisi)
                            .bindPopup(`<b>Perkiraan Lokasi</b><br>${lokasiText}`).openPopup();
                    }
                })
                .catch(e => console.log('Geocoding fallback:', e));
        }
    </script>

    <?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>