<?php
require_once '../../config/init.php';
requireLogin('admin');
$db = getDB();

// Handle verifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)$_POST['id_laporan'];
    if ($_POST['action'] === 'verifikasi') {
        $id_polisi = (int)$_POST['id_polisi'];
        $catatan   = sanitize($_POST['catatan_admin'] ?? '');
        $db->prepare("UPDATE pengaduan SET status='Diverifikasi',id_admin=?,id_polisi=?,catatan_admin=? WHERE id_laporan=?")->execute([$_SESSION['user_id'], $id_polisi, $catatan, $id]);
        setFlash('success', 'Laporan berhasil diverifikasi dan ditugaskan ke Polisi Hutan.');
    } elseif ($_POST['action'] === 'tolak') {
        $catatan = sanitize($_POST['catatan_admin'] ?? '');
        $db->prepare("UPDATE pengaduan SET status='Ditolak',id_admin=?,catatan_admin=? WHERE id_laporan=?")->execute([$_SESSION['user_id'], $catatan, $id]);
        setFlash('danger', 'Laporan telah ditolak.');
    }
    redirect(BASE_URL . 'pages/admin/verifikasi_laporan.php');
}

// Filter
$search  = sanitize($_GET['search'] ?? '');
$filterJ = (int)($_GET['id_jenis'] ?? 0);

$sql = "SELECT p.*,m.nama as pelapor,j.nama_jenis,h.nama_hutan FROM pengaduan p
        JOIN masyarakat m ON p.id_user=m.id_user
        JOIN jenis_kejahatan j ON p.id_jenis=j.id_jenis
        JOIN jenis_kawasan_hutan h ON p.id_hutan=h.id_hutan
        WHERE p.status='Baru'";
$params = [];
if ($search) {
    $sql .= " AND (m.nama LIKE ? OR p.lokasi LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%"]);
}
if ($filterJ) {
    $sql .= " AND p.id_jenis=?";
    $params[] = $filterJ;
}
$sql .= " ORDER BY p.tanggal_lapor DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();

$polisiList = $db->query("SELECT * FROM polisi_hutan ORDER BY nama")->fetchAll();
$jenisList  = $db->query("SELECT * FROM jenis_kejahatan ORDER BY nama_jenis")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Verifikasi Laporan — SIKAWAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px)
        }

        .modal-overlay.show {
            display: flex
        }

        .modal-box {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .3);
            overflow: hidden
        }

        .modal-head {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .modal-head h3 {
            font-size: 16px;
            font-weight: 700
        }

        .modal-body {
            padding: 24px
        }

        .modal-foot {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            justify-content: flex-end
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #94a3b8
        }

        .close-btn:hover {
            color: #1e293b
        }
    </style>
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
                    <h1>Verifikasi Laporan</h1>
                    <p>Periksa dan verifikasi laporan baru dari masyarakat</p>
                </div>
            </div>
            <div class="page-body">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div>
                <?php endif; ?>

                <form method="GET" class="filter-bar">
                    <div class="form-group">
                        <label class="form-label">Cari Pelapor/Lokasi</label>
                        <div class="input-group"><i class="fas fa-search input-icon"></i>
                            <input type="text" name="search" class="form-control" placeholder="Cari..." value="<?= $search ?>">
                        </div>
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
                    <a href="verifikasi_laporan.php" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-clipboard-check" style="color:#1a7a3f;margin-right:8px"></i>Laporan Perlu Diverifikasi
                            <span style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:11px;padding:3px 10px;border-radius:20px;margin-left:8px"><?= count($laporan) ?> laporan</span>
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
                                        <th>Lokasi</th>
                                        <th>Tgl Lapor</th>
                                        <th>Bukti Foto</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= sanitize($r['pelapor']) ?></strong><br><small style="color:#94a3b8"><?= sanitize($r['nama_hutan']) ?></small></td>
                                            <td><?= sanitize($r['nama_jenis']) ?></td>
                                            <td><span style="max-width:160px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= sanitize($r['lokasi']) ?>"><?= sanitize($r['lokasi']) ?></span></td>
                                            <td><?= formatTanggal($r['tanggal_lapor']) ?></td>
                                            <td>
                                                <?php if ($r['bukti_foto']): ?>
                                                    <img src="<?= BASE_URL ?><?= $r['bukti_foto'] ?>" class="foto-thumb" onclick="showImg('<?= BASE_URL ?><?= $r['bukti_foto'] ?>','<?= sanitize($r['pelapor']) ?>',<?= $r['id_laporan'] ?>)" title="Klik untuk memperbesar">
                                                <?php else: ?><span style="color:#94a3b8;font-size:12px">Tidak ada</span><?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="detail_pengaduan.php?id=<?= $r['id_laporan'] ?>" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                                                <button onclick="openVerif(<?= $r['id_laporan'] ?>,<?= json_encode(sanitize($r['pelapor'])) ?>)" class="btn btn-primary btn-sm" title="Verifikasi"><i class="fas fa-check"></i> Verifikasi</button>
                                                <button onclick="openTolak(<?= $r['id_laporan'] ?>,<?= json_encode(sanitize($r['pelapor'])) ?>)" class="btn btn-danger btn-sm" title="Tolak"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-check-circle"></i></div>
                                <h3>Semua laporan sudah diverifikasi!</h3>
                                <p>Tidak ada laporan baru yang perlu diverifikasi.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Verifikasi -->
        <div class="modal-overlay" id="mdVerif">
            <div class="modal-box">
                <div class="modal-head">
                    <h3><i class="fas fa-check-circle" style="color:#22c55e;margin-right:8px"></i>Verifikasi Laporan</h3>
                    <button class="close-btn" onclick="closeModal('mdVerif')"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="verifikasi">
                        <input type="hidden" name="id_laporan" id="vId">
                        <p style="font-size:13px;color:#64748b;margin-bottom:16px">Verifikasi laporan dari: <strong id="vName"></strong></p>
                        <div class="form-group">
                            <label class="form-label">Tugaskan ke Polisi Hutan <span style="color:red">*</span></label>
                            <select name="id_polisi" class="form-select" required>
                                <option value="">-- Pilih Polisi Hutan --</option>
                                <?php foreach ($polisiList as $p): ?>
                                    <option value="<?= $p['id_polisi'] ?>"><?= sanitize($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Catatan Admin</label>
                            <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Catatan untuk polisi hutan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button type="button" onclick="closeModal('mdVerif')" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Verifikasi & Tugaskan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Tolak -->
        <div class="modal-overlay" id="mdTolak">
            <div class="modal-box">
                <div class="modal-head">
                    <h3><i class="fas fa-times-circle" style="color:#ef4444;margin-right:8px"></i>Tolak Laporan</h3>
                    <button class="close-btn" onclick="closeModal('mdTolak')"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="tolak">
                        <input type="hidden" name="id_laporan" id="tId">
                        <div class="alert alert-warning" style="margin-bottom:16px"><i class="fas fa-exclamation-triangle"></i> Laporan akan ditandai sebagai <strong>Ditolak</strong>.</div>
                        <div class="form-group">
                            <label class="form-label">Alasan Penolakan <span style="color:red">*</span></label>
                            <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Berikan alasan penolakan..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button type="button" onclick="closeModal('mdTolak')" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak Laporan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Foto -->
        <div class="modal-overlay" id="mdFoto">
            <div style="max-width:700px;width:100%;padding:16px">
                <button onclick="closeModal('mdFoto')" style="position:fixed;top:16px;right:16px;background:rgba(255,255,255,.9);border:none;width:40px;height:40px;border-radius:50%;cursor:pointer;font-size:18px;z-index:10000"><i class="fas fa-times"></i></button>
                <img id="bigImg" src="" style="max-width:100%;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.5)">
            </div>
        </div>

        <script>
            function openVerif(id, name) {
                document.getElementById('vId').value = id;
                document.getElementById('vName').textContent = name;
                document.getElementById('mdVerif').classList.add('show');
            }

            function openTolak(id, name) {
                document.getElementById('tId').value = id;
                document.getElementById('mdTolak').classList.add('show');
            }

            function closeModal(id) {
                document.getElementById(id).classList.remove('show');
            }

            function showImg(src) {
                document.getElementById('bigImg').src = src;
                document.getElementById('mdFoto').classList.add('show');
            }
            document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('show');
            }));
        </script>

<?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>