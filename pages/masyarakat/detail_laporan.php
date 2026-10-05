<?php
require_once '../../config/init.php';
requireLogin('masyarakat');
$db  = getDB();
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . 'pages/masyarakat/riwayat_laporan.php');

$stmt = $db->prepare("SELECT p.*,j.nama_jenis,h.nama_hutan,h.lokasi_hutan,ph.nama as nama_polisi FROM pengaduan p JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan LEFT JOIN polisi_hutan ph ON p.id_polisi=ph.id_polisi WHERE p.id_laporan=? AND p.id_user=?");
$stmt->execute([$id, $uid]);
$r = $stmt->fetch();
if (!$r) redirect(BASE_URL . 'pages/masyarakat/riwayat_laporan.php');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Detail Laporan — SIKAWAS</title>
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
        <?php include ROOT_PATH . 'includes/sidebar_masyarakat.php'; ?>
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-left">
                    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div class="topbar-title">
                        <h1>Detail Laporan</h1>
                        <p>Laporan #<?= $r['id_laporan'] ?></p>
                    </div>
                </div>
                <div class="topbar-actions">
                    <a href="riwayat_laporan.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="page-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                    <!-- Kiri: Detail Laporan & Bukti Foto -->
                    <div>
                        <div class="card" style="margin-bottom:20px">
                            <div class="card-header">
                                <h3><i class="fas fa-file-alt" style="color:#1a7a3f;margin-right:8px"></i>Detail Laporan Saya</h3><?= getStatusBadge($r['status']) ?>
                            </div>
                            <div class="card-body">
                                <div class="info-row"><span class="info-label">ID Laporan</span><span class="info-val">#<?= $r['id_laporan'] ?></span></div>
                                <div class="info-row"><span class="info-label">Jenis Kejahatan</span><span class="info-val"><?= sanitize($r['nama_jenis']) ?></span></div>
                                <div class="info-row"><span class="info-label">Kawasan Hutan</span><span class="info-val"><?= sanitize($r['nama_hutan']) ?></span></div>
                                <div class="info-row"><span class="info-label">Lokasi</span><span class="info-val"><?= sanitize($r['lokasi']) ?></span></div>
                                <div class="info-row"><span class="info-label">Tgl Kejadian</span><span class="info-val"><?= formatTanggal($r['tanggal_kejadian']) ?></span></div>
                                <div class="info-row"><span class="info-label">Tgl Dilaporkan</span><span class="info-val"><?= formatTanggal($r['tanggal_lapor']) ?></span></div>
                                <div class="info-row" style="flex-direction:column;gap:6px"><span class="info-label">Deskripsi</span><span class="info-val" style="background:#f8fafc;padding:12px;border-radius:8px;line-height:1.7"><?= nl2br(sanitize($r['deskripsi'])) ?></span></div>
                            </div>
                        </div>

                        <!-- Peta Lokasi Kejadian (Maps) -->
                        <div class="card" style="margin-bottom:20px">
                            <div class="card-header">
                                <h3><i class="fas fa-map-marked-alt" style="color:#1a7a3f;margin-right:8px"></i>Peta Lokasi Kejadian</h3>
                                <?php if (!empty($r['latitude']) && !empty($r['longitude'])): ?>
                                    <a href="https://www.google.com/maps?q=<?= $r['latitude'] ?>,<?= $r['longitude'] ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size:11px">
                                        <i class="fas fa-external-link-alt"></i> Buka Google Maps
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body" style="padding:0">
                                <div id="mapMasyarakat" style="height:270px;width:100%;z-index:1"></div>
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
                                        <strong>Lokasi:</strong> <?= sanitize($r['lokasi']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($r['bukti_foto']): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-camera" style="color:#1a7a3f;margin-right:8px"></i>Bukti Foto</h3>
                                </div>
                                <div class="card-body"><img src="<?= BASE_URL ?><?= sanitize($r['bukti_foto']) ?>" style="width:100%;border-radius:10px;border:2px solid #e2e8f0;cursor:zoom-in;max-height:280px;object-fit:cover" onclick="window.open('<?= BASE_URL ?><?= sanitize($r['bukti_foto']) ?>','_blank')"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kanan: Status Penanganan -->
                    <div>
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-shield-alt" style="color:#1a7a3f;margin-right:8px"></i>Status Penanganan</h3>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-dot done"></div>
                                        <div class="timeline-content"><strong>Laporan Dikirim</strong>
                                            <p><?= formatTanggal($r['tanggal_lapor']) ?></p>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-dot <?= in_array($r['status'], ['Diverifikasi', 'Diproses', 'Selesai']) ? 'done' : 'pending' ?>"></div>
                                        <div class="timeline-content"><strong>Diverifikasi Admin</strong><?php if (in_array($r['status'], ['Diverifikasi', 'Diproses', 'Selesai'])): ?><p style="color:#16a34a">✓ Laporan telah diverifikasi</p><?php else: ?><p>Menunggu verifikasi dari Admin</p><?php endif; ?></div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-dot <?= in_array($r['status'], ['Diproses', 'Selesai']) ? 'done' : 'pending' ?>"></div>
                                        <div class="timeline-content"><strong>Ditangani Polisi Hutan</strong><?php if ($r['nama_polisi']): ?><p><?= sanitize($r['nama_polisi']) ?></p><?php else: ?><p style="color:#94a3b8">Menunggu penugasan</p><?php endif; ?></div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-dot <?= $r['status'] === 'Selesai' ? 'done' : 'pending' ?>"></div>
                                        <div class="timeline-content"><strong>Selesai</strong><?php if ($r['status'] === 'Selesai'): ?><p style="color:#16a34a">✓ Laporan telah diselesaikan</p><?php else: ?><p style="color:#94a3b8">Belum selesai</p><?php endif; ?></div>
                                    </div>
                                </div>
                                <?php if ($r['catatan_admin']): ?>
                                    <div style="margin-top:16px;background:#f0fdf4;padding:14px;border-radius:10px;border-left:4px solid #22c55e">
                                        <p style="font-size:12px;font-weight:600;color:#15803d;margin-bottom:4px"><i class="fas fa-user-shield"></i> Catatan Admin:</p>
                                        <p style="font-size:13px"><?= nl2br(sanitize($r['catatan_admin'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if ($r['catatan_polisi']): ?>
                                    <div style="margin-top:12px;background:#eff6ff;padding:14px;border-radius:10px;border-left:4px solid #3b82f6">
                                        <p style="font-size:12px;font-weight:600;color:#1d4ed8;margin-bottom:4px"><i class="fas fa-hard-hat"></i> Catatan Polisi Hutan:</p>
                                        <p style="font-size:13px"><?= nl2br(sanitize($r['catatan_polisi'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if ($r['status'] === 'Ditolak'): ?>
                                    <div style="margin-top:12px;background:#fef2f2;padding:14px;border-radius:10px;border-left:4px solid #ef4444">
                                        <p style="font-size:12px;font-weight:600;color:#dc2626;margin-bottom:4px"><i class="fas fa-times-circle"></i> Laporan Ditolak</p>
                                        <p style="font-size:13px">Laporan Anda telah ditolak oleh admin. Hubungi kantor Dinas Kehutanan setempat untuk info lebih lanjut.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
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

        const mapMasyarakat = L.map('mapMasyarakat');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(mapMasyarakat);

        if (reportLat !== null && reportLng !== null) {
            mapMasyarakat.setView([reportLat, reportLng], 14);
            const m = L.marker([reportLat, reportLng]).addTo(mapMasyarakat);
            m.bindPopup(`<b>Titik Kejadian #${<?= (int)$r['id_laporan'] ?>}</b><br><b>Jenis:</b> ${jenisText}<br><b>Lokasi:</b> ${lokasiText}<br><b>Koordinat:</b> ${reportLat}, ${reportLng}`).openPopup();
        } else {
            mapMasyarakat.setView([-4.18, 121.60], 7);
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(lokasiText)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        mapMasyarakat.setView([lat, lon], 12);
                        L.marker([lat, lon]).addTo(mapMasyarakat)
                            .bindPopup(`<b>Perkiraan Lokasi</b><br>${lokasiText}`).openPopup();
                    }
                })
                .catch(e => console.log('Geocoding fallback:', e));
        }
    </script>

    <?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>