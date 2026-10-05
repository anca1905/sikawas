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

// Data titik peta
$mapPoints = [];
foreach ($laporan as $r) {
    if (!empty($r['latitude']) && !empty($r['longitude'])) {
        $mapPoints[] = [
            'id'       => $r['id_laporan'],
            'lat'      => (float)$r['latitude'],
            'lng'      => (float)$r['longitude'],
            'pelapor'  => $r['pelapor'],
            'jenis'    => $r['nama_jenis'],
            'kawasan'  => $r['nama_hutan'],
            'lokasi'   => $r['lokasi'],
            'status'   => $r['status'],
            'tanggal'  => formatTanggal($r['tanggal_kejadian']),
            'url'      => 'detail_pengaduan.php?id=' . $r['id_laporan']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Daftar Pengaduan — SIKAWAS | Polisi Hutan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
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
                    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                        <h3><i class="fas fa-list-ul" style="color:#1a7a3f;margin-right:8px"></i>Pengaduan Saya (<?= count($laporan) ?>)</h3>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="btn btn-sm btn-primary" id="btnViewTable" onclick="switchView('table')"><i class="fas fa-table"></i> Tabel</button>
                            <button type="button" class="btn btn-sm btn-secondary" id="btnViewMap" onclick="switchView('map')"><i class="fas fa-map-marked-alt"></i> Peta Lokasi (<?= count($mapPoints) ?>)</button>
                        </div>
                    </div>

                    <!-- Tampilan Peta Sebaran untuk Polisi -->
                    <div id="mapViewContainer" style="display:none;padding:16px">
                        <div style="font-size:13px;color:#64748b;margin-bottom:12px;display:flex;align-items:center;gap:6px">
                            <i class="fas fa-info-circle" style="color:#1a7a3f"></i> Menampilkan <strong><?= count($mapPoints) ?></strong> titik kejadian dengan koordinat GPS dari tugas Anda.
                        </div>
                        <div id="polisiMap" style="height:480px;width:100%;border-radius:10px;border:1px solid #e2e8f0;z-index:1"></div>
                    </div>

                    <!-- Tampilan Tabel -->
                    <div class="table-responsive" id="tableViewContainer">
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
                                            <td>
                                                <div style="display:flex;align-items:center;gap:6px">
                                                    <?php if (!empty($r['latitude']) && !empty($r['longitude'])): ?>
                                                        <a href="detail_pengaduan.php?id=<?= $r['id_laporan'] ?>" title="Lihat peta: <?= $r['latitude'] ?>, <?= $r['longitude'] ?>" style="color:#ef4444;font-size:13px"><i class="fas fa-map-marker-alt"></i></a>
                                                    <?php endif; ?>
                                                    <span style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= sanitize($r['lokasi']) ?>"><?= sanitize($r['lokasi']) ?></span>
                                                </div>
                                            </td>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const mapData = <?= json_encode($mapPoints) ?>;
        let polisiMapInstance = null;

        function switchView(mode) {
            const btnTable = document.getElementById('btnViewTable');
            const btnMap   = document.getElementById('btnViewMap');
            const tableDiv = document.getElementById('tableViewContainer');
            const mapDiv   = document.getElementById('mapViewContainer');

            if (mode === 'map') {
                btnTable.className = 'btn btn-sm btn-secondary';
                btnMap.className   = 'btn btn-sm btn-primary';
                tableDiv.style.display = 'none';
                mapDiv.style.display   = 'block';

                if (!polisiMapInstance) {
                    polisiMapInstance = L.map('polisiMap').setView([-4.18, 121.60], 7);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(polisiMapInstance);

                    if (mapData.length > 0) {
                        const bounds = [];
                        mapData.forEach(p => {
                            const marker = L.marker([p.lat, p.lng]).addTo(polisiMapInstance);
                            marker.bindPopup(`
                                <div style="font-size:12px;line-height:1.5">
                                    <strong style="color:#1a7a3f;font-size:13px">Laporan #${p.id}</strong><br>
                                    <b>Jenis:</b> ${p.jenis}<br>
                                    <b>Kawasan:</b> ${p.kawasan}<br>
                                    <b>Pelapor:</b> ${p.pelapor}<br>
                                    <b>Status:</b> ${p.status}<br>
                                    <a href="${p.url}" class="btn btn-primary btn-sm" style="margin-top:6px;display:inline-block;padding:2px 8px;font-size:11px">Tindak Lanjut</a>
                                </div>
                            `);
                            bounds.push([p.lat, p.lng]);
                        });
                        polisiMapInstance.fitBounds(bounds, { padding: [40, 40] });
                    }
                } else {
                    setTimeout(() => polisiMapInstance.invalidateSize(), 200);
                }
            } else {
                btnTable.className = 'btn btn-sm btn-primary';
                btnMap.className   = 'btn btn-sm btn-secondary';
                tableDiv.style.display = 'block';
                mapDiv.style.display   = 'none';
            }
        }
    </script>

    <?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>