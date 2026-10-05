<?php
require_once '../../config/init.php';
requireLogin('masyarakat');
$db  = getDB();
$uid = $_SESSION['user_id'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal   = sanitize($_POST['tanggal_kejadian'] ?? '');
    $lokasi    = sanitize($_POST['lokasi'] ?? '');
    $latitude  = sanitize($_POST['latitude'] ?? '');
    $longitude = sanitize($_POST['longitude'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    
    $id_jenis_raw = $_POST['id_jenis'] ?? '';
    $jenis_manual = trim($_POST['jenis_manual'] ?? '');
    $id_hutan_raw = $_POST['id_hutan'] ?? '';
    $hutan_manual = trim($_POST['hutan_manual'] ?? '');

    // Handle Jenis Kejahatan (Pilihan atau Ketik Manual)
    $id_jenis = 0;
    if ($id_jenis_raw === 'manual' || (!empty($jenis_manual) && empty($id_jenis_raw))) {
        if (!empty($jenis_manual)) {
            $stmtJenis = $db->prepare("SELECT id_jenis FROM jenis_kejahatan WHERE LOWER(TRIM(nama_jenis)) = LOWER(?) LIMIT 1");
            $stmtJenis->execute([$jenis_manual]);
            $existingJenis = $stmtJenis->fetchColumn();
            if ($existingJenis) {
                $id_jenis = (int)$existingJenis;
            } else {
                $insJ = $db->prepare("INSERT INTO jenis_kejahatan (nama_jenis, keterangan) VALUES (?, 'Ditambahkan manual oleh masyarakat')");
                $insJ->execute([$jenis_manual]);
                $id_jenis = (int)$db->lastInsertId();
            }
        }
    } else {
        $id_jenis = (int)$id_jenis_raw;
    }

    // Handle Kawasan Hutan (Pilihan atau Ketik Manual)
    $id_hutan = 0;
    if ($id_hutan_raw === 'manual' || (!empty($hutan_manual) && empty($id_hutan_raw))) {
        if (!empty($hutan_manual)) {
            $stmtHutan = $db->prepare("SELECT id_hutan FROM jenis_kawasan_hutan WHERE LOWER(TRIM(nama_hutan)) = LOWER(?) LIMIT 1");
            $stmtHutan->execute([$hutan_manual]);
            $existingHutan = $stmtHutan->fetchColumn();
            if ($existingHutan) {
                $id_hutan = (int)$existingHutan;
            } else {
                $insH = $db->prepare("INSERT INTO jenis_kawasan_hutan (nama_hutan, lokasi_hutan, keterangan) VALUES (?, ?, 'Ditambahkan manual oleh masyarakat')");
                $insH->execute([$hutan_manual, $lokasi ?: 'Kawasan Hutan']);
                $id_hutan = (int)$db->lastInsertId();
            }
        }
    } else {
        $id_hutan = (int)$id_hutan_raw;
    }

    if (!$tanggal || !$lokasi || !$deskripsi || !$id_jenis || !$id_hutan) {
        $error = 'Semua field wajib harus diisi. Pastikan jenis kejahatan dan kawasan hutan telah dipilih atau diketik manual.';
    } else {
        $fotoPath = uploadFoto($_FILES['bukti_foto'] ?? null, 'uploads');
        $db->prepare("INSERT INTO pengaduan (tanggal_kejadian,lokasi,latitude,longitude,deskripsi,bukti_foto,status,id_user,id_jenis,id_hutan) VALUES (?,?,?,?,?,?,?,?,?,?)")->execute([$tanggal, $lokasi, $latitude, $longitude, $deskripsi, $fotoPath, 'Baru', $uid, $id_jenis, $id_hutan]);
        setFlash('success', 'Laporan berhasil dikirim! Kami akan segera memverifikasi laporan Anda.');
        redirect(BASE_URL . 'pages/masyarakat/riwayat_laporan.php');
    }
}

$jenisList = $db->query("SELECT * FROM jenis_kejahatan ORDER BY nama_jenis")->fetchAll();
$hutanList = $db->query("SELECT * FROM jenis_kawasan_hutan ORDER BY nama_hutan")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Buat Laporan — SIKAWAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .laporan-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .laporan-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        .photo-upload-area {
            border: 2px dashed var(--border);
            border-radius: 10px;
            padding: 36px 20px;
            text-align: center;
            transition: all .3s;
            cursor: pointer;
            background: #fafbfc;
        }

        .photo-upload-area:hover,
        .photo-upload-area.drag {
            border-color: #1a7a3f;
            background: #f0fdf4;
        }

        .photo-upload-area i {
            font-size: 44px;
            color: #cbd5e1;
            margin-bottom: 10px;
            display: block;
        }

        .photo-upload-area.has-file i {
            color: #1a7a3f;
        }

        .toggle-btn {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .toggle-btn:hover, .toggle-btn.active {
            background: #e0f2fe;
            border-color: #0284c7;
            color: #0369a1;
        }

        .map-search-toolbar {
            padding: 10px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .map-coords-bar {
            padding: 10px 12px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
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
                        <h1>Buat Laporan Baru</h1>
                        <p>Laporkan kejahatan lingkungan yang Anda temukan</p>
                    </div>
                </div>
                <div class="topbar-actions">
                    <a href="riwayat_laporan.php" class="btn btn-secondary btn-sm"><i class="fas fa-history"></i> <span>Riwayat</span></a>
                </div>
            </div>
            <div class="page-body">
                <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> Isi formulir di bawah ini dengan lengkap dan akurat. Laporan Anda akan diverifikasi oleh Admin sebelum ditindaklanjuti.</div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="laporan-grid">
                        <!-- Kiri: Informasi Kejadian -->
                        <div>
                            <div class="card" style="margin-bottom:16px">
                                <div class="card-header">
                                    <h3><i class="fas fa-info-circle" style="color:#1a7a3f;margin-right:8px"></i>Informasi Kejadian</h3>
                                </div>
                                <div class="card-body">
                                    
                                    <!-- Jenis Kejahatan (Pilih / Ketik Manual) -->
                                    <div class="form-group">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                                            <label class="form-label" style="margin-bottom:0">Jenis Kejahatan <span class="required">*</span></label>
                                            <button type="button" class="toggle-btn" id="btnToggleJenis" onclick="toggleManualJenis()">
                                                <i class="fas fa-pen"></i> <span>Ketik Manual</span>
                                            </button>
                                        </div>
                                        <div class="input-group" id="groupSelectJenis"><i class="fas fa-tag input-icon"></i>
                                            <select name="id_jenis" id="selectJenis" class="form-select" style="padding-left:38px" onchange="onJenisChange(this)">
                                                <option value="">-- Pilih Jenis Kejahatan --</option>
                                                <?php foreach ($jenisList as $j): ?>
                                                    <option value="<?= $j['id_jenis'] ?>" <?= (($_POST['id_jenis'] ?? '') == $j['id_jenis']) ? 'selected' : '' ?>><?= sanitize($j['nama_jenis']) ?></option>
                                                <?php endforeach; ?>
                                                <option value="manual" <?= (($_POST['id_jenis'] ?? '') === 'manual') ? 'selected' : '' ?>>✍️ Ketik Manual (Jenis Lainnya)...</option>
                                            </select>
                                        </div>
                                        <div id="containerJenisManual" style="display:<?= (!empty($_POST['jenis_manual']) || ($_POST['id_jenis'] ?? '') === 'manual') ? 'block' : 'none' ?>;margin-top:8px">
                                            <div class="input-group"><i class="fas fa-pen input-icon"></i>
                                                <input type="text" name="jenis_manual" id="jenis_manual" class="form-control" style="padding-left:38px" placeholder="Ketik nama jenis kejahatan baru..." value="<?= sanitize($_POST['jenis_manual'] ?? '') ?>">
                                            </div>
                                            <div class="form-hint" style="color:#1a7a3f;font-size:11px;margin-top:4px"><i class="fas fa-check-circle"></i> Mode input manual aktif untuk jenis kejahatan.</div>
                                        </div>
                                    </div>

                                    <!-- Kawasan Hutan (Pilih / Ketik Manual) -->
                                    <div class="form-group">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                                            <label class="form-label" style="margin-bottom:0">Kawasan Hutan <span class="required">*</span></label>
                                            <button type="button" class="toggle-btn" id="btnToggleHutan" onclick="toggleManualHutan()">
                                                <i class="fas fa-pen"></i> <span>Ketik Manual</span>
                                            </button>
                                        </div>
                                        <div class="input-group" id="groupSelectHutan"><i class="fas fa-map input-icon"></i>
                                            <select name="id_hutan" id="selectHutan" class="form-select" style="padding-left:38px" onchange="onHutanChange(this)">
                                                <option value="">-- Pilih Kawasan Hutan --</option>
                                                <?php foreach ($hutanList as $h): ?>
                                                    <option value="<?= $h['id_hutan'] ?>" <?= (($_POST['id_hutan'] ?? '') == $h['id_hutan']) ? 'selected' : '' ?>><?= sanitize($h['nama_hutan']) ?> — <?= sanitize($h['lokasi_hutan']) ?></option>
                                                <?php endforeach; ?>
                                                <option value="manual" <?= (($_POST['id_hutan'] ?? '') === 'manual') ? 'selected' : '' ?>>✍️ Ketik Manual (Kawasan Lainnya)...</option>
                                            </select>
                                        </div>
                                        <div id="containerHutanManual" style="display:<?= (!empty($_POST['hutan_manual']) || ($_POST['id_hutan'] ?? '') === 'manual') ? 'block' : 'none' ?>;margin-top:8px">
                                            <div class="input-group"><i class="fas fa-tree input-icon"></i>
                                                <input type="text" name="hutan_manual" id="hutan_manual" class="form-control" style="padding-left:38px" placeholder="Ketik nama kawasan hutan baru..." value="<?= sanitize($_POST['hutan_manual'] ?? '') ?>">
                                            </div>
                                            <div class="form-hint" style="color:#1a7a3f;font-size:11px;margin-top:4px"><i class="fas fa-check-circle"></i> Mode input manual aktif untuk kawasan hutan.</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Tanggal Kejadian <span class="required">*</span></label>
                                        <div class="input-group"><i class="fas fa-calendar input-icon"></i>
                                            <input type="date" name="tanggal_kejadian" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= sanitize($_POST['tanggal_kejadian'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Lokasi Kejadian (Detail Alamat) <span class="required">*</span></label>
                                        <div class="input-group"><i class="fas fa-map-marker-alt input-icon" style="top:12px;transform:none"></i>
                                            <textarea name="lokasi" id="textareaLokasi" class="form-control" rows="3" style="padding-left:38px" placeholder="cth: Blok C Sektor 3, dekat sungai..." required><?= sanitize($_POST['lokasi'] ?? '') ?></textarea>
                                        </div>
                                        <div class="form-hint">Otomatis terisi ketika memilih titik di peta, atau ketik detail alamat secara langsung.</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Deskripsi Kejadian <span class="required">*</span></label>
                                        <textarea name="deskripsi" class="form-control" rows="5" placeholder="Ceritakan secara detail kejadian yang Anda temukan..." required><?= sanitize($_POST['deskripsi'] ?? '') ?></textarea>
                                        <div class="form-hint">Semakin detail deskripsi, semakin mudah bagi petugas untuk menindaklanjuti.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kanan: Peta, Bukti Foto, Komitmen -->
                        <div>
                            <div class="card" style="margin-bottom:16px">
                                <div class="card-header">
                                    <h3><i class="fas fa-map-marked-alt" style="color:#1a7a3f;margin-right:8px"></i>Peta Lokasi Kejadian</h3>
                                    <span style="font-size:11px;color:#64748b">Cari & Tandai Titik</span>
                                </div>
                                <div class="card-body" style="padding:0">
                                    
                                    <!-- Search & Current Location Toolbar -->
                                    <div class="map-search-toolbar">
                                        <div style="flex:1;min-width:180px;position:relative">
                                            <input type="text" id="mapSearchInput" class="form-control" style="padding-left:32px;font-size:12px;height:34px" placeholder="Cari nama tempat (cth: Pomalaa, Kolaka)..." onkeydown="if(event.key==='Enter'){event.preventDefault();cariLokasi();}">
                                            <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12px"></i>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="cariLokasi()" style="height:34px;padding:0 12px;font-size:12px">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="gunakanLokasiSaatIni()" style="height:34px;padding:0 12px;font-size:12px;background:#059669;border-color:#059669;white-space:nowrap" title="Deteksi GPS perangkat">
                                            <i class="fas fa-crosshairs"></i> Gunakan Lokasi Saat Ini
                                        </button>
                                    </div>

                                    <!-- Status / feedback message -->
                                    <div id="searchStatus" style="display:none;padding:8px 12px;font-size:12px;background:#f0fdf4;border-bottom:1px solid #bbf7d0"></div>

                                    <!-- Peta Leaflet -->
                                    <div id="map" style="height: 290px; width: 100%; z-index: 1;"></div>

                                    <!-- Koordinat Terisi Otomatis -->
                                    <div class="map-coords-bar">
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:6px">
                                            <div>
                                                <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px">
                                                    <i class="fas fa-map-pin" style="color:#ef4444"></i> Latitude (Otomatis)
                                                </label>
                                                <input type="text" name="latitude" id="latitude" class="form-control" readonly placeholder="-4.xxxxxx" style="background:#fff;font-weight:600;font-size:12px" value="<?= sanitize($_POST['latitude'] ?? '') ?>">
                                            </div>
                                            <div>
                                                <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px">
                                                    <i class="fas fa-map-pin" style="color:#ef4444"></i> Longitude (Otomatis)
                                                </label>
                                                <input type="text" name="longitude" id="longitude" class="form-control" readonly placeholder="121.xxxxxx" style="background:#fff;font-weight:600;font-size:12px" value="<?= sanitize($_POST['longitude'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:6px">
                                            <i class="fas fa-info-circle" style="color:#0284c7"></i> Cari lokasi, klik titik kejadian pada peta, atau klik <strong>Gunakan Lokasi Saat Ini</strong> untuk mengisi koordinat otomatis.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card" style="margin-bottom:16px">
                                <div class="card-header">
                                    <h3><i class="fas fa-camera" style="color:#1a7a3f;margin-right:8px"></i>Bukti Foto</h3>
                                    <span style="font-size:11px;color:#64748b">Opsional</span>
                                </div>
                                <div class="card-body">
                                    <div class="photo-upload-area" id="uploadArea" onclick="document.getElementById('fotoInput').click()">
                                        <i class="fas fa-cloud-upload-alt" id="uploadIcon"></i>
                                        <p style="font-size:14px;font-weight:600;color:#374151;margin-bottom:4px" id="uploadText">Klik untuk unggah foto</p>
                                        <p style="font-size:12px;color:#94a3b8">JPG, PNG, GIF, WEBP • Maks 5 MB</p>
                                        <input type="file" name="bukti_foto" id="fotoInput" accept="image/*" style="display:none" onchange="previewFoto(this)">
                                    </div>
                                    <img id="previewImg" style="display:none;width:100%;border-radius:10px;margin-top:14px;border:2px solid #e2e8f0;max-height:280px;object-fit:cover">
                                    <button type="button" id="removeBtn" style="display:none;margin-top:8px;width:100%" class="btn btn-secondary btn-sm" onclick="removeFoto()"><i class="fas fa-times"></i> Hapus Foto</button>
                                    <div class="alert alert-warning" style="margin-top:14px;font-size:12px"><i class="fas fa-exclamation-triangle"></i> Foto yang jelas dan relevan akan membantu proses verifikasi dan penanganan laporan Anda.</div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-shield-alt" style="color:#1a7a3f;margin-right:8px"></i>Komitmen Pelapor</h3>
                                </div>
                                <div class="card-body">
                                    <div style="display:flex;align-items:flex-start;gap:12px;padding:14px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0">
                                        <input type="checkbox" id="komitmen" required style="margin-top:3px;accent-color:#1a7a3f;width:16px;height:16px;flex-shrink:0">
                                        <label for="komitmen" style="font-size:13px;color:#1e293b;cursor:pointer;line-height:1.6">Saya menyatakan bahwa informasi yang saya laporkan adalah <strong>benar dan akurat</strong> sesuai dengan yang saya temukan atau saksikan secara langsung.</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px">
                        <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Preview Foto Upload
        function previewFoto(input) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('previewImg').style.display = 'block';
                document.getElementById('removeBtn').style.display = 'block';
                document.getElementById('uploadArea').style.border = '2px dashed #1a7a3f';
                document.getElementById('uploadIcon').style.color = '#1a7a3f';
                document.getElementById('uploadText').textContent = file.name;
            };
            reader.readAsDataURL(file);
        }

        function removeFoto() {
            document.getElementById('fotoInput').value = '';
            document.getElementById('previewImg').style.display = 'none';
            document.getElementById('removeBtn').style.display = 'none';
            document.getElementById('uploadArea').style.border = '2px dashed #e2e8f0';
            document.getElementById('uploadIcon').style.color = '#cbd5e1';
            document.getElementById('uploadText').textContent = 'Klik untuk unggah foto';
        }

        const uploadArea = document.getElementById('uploadArea');
        uploadArea.addEventListener('dragover', e => {
            e.preventDefault();
            uploadArea.classList.add('drag');
        });
        uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag'));
        uploadArea.addEventListener('drop', e => {
            e.preventDefault();
            uploadArea.classList.remove('drag');
            const f = e.dataTransfer.files[0];
            if (f && f.type.startsWith('image/')) {
                const dt = new DataTransfer();
                dt.items.add(f);
                document.getElementById('fotoInput').files = dt.files;
                previewFoto(document.getElementById('fotoInput'));
            }
        });

        // Toggle Manual Input Jenis Kejahatan
        function toggleManualJenis() {
            const sel = document.getElementById('selectJenis');
            const container = document.getElementById('containerJenisManual');
            const input = document.getElementById('jenis_manual');
            const btn = document.getElementById('btnToggleJenis');

            if (container.style.display === 'none') {
                container.style.display = 'block';
                sel.value = 'manual';
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-list"></i> <span>Pilih dari Daftar</span>';
                input.focus();
            } else {
                container.style.display = 'none';
                if (sel.value === 'manual') sel.value = '';
                btn.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-pen"></i> <span>Ketik Manual</span>';
                input.value = '';
            }
        }

        function onJenisChange(sel) {
            const container = document.getElementById('containerJenisManual');
            const input = document.getElementById('jenis_manual');
            const btn = document.getElementById('btnToggleJenis');

            if (sel.value === 'manual') {
                container.style.display = 'block';
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-list"></i> <span>Pilih dari Daftar</span>';
                input.focus();
            } else {
                container.style.display = 'none';
                btn.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-pen"></i> <span>Ketik Manual</span>';
                input.value = '';
            }
        }

        // Toggle Manual Input Kawasan Hutan
        function toggleManualHutan() {
            const sel = document.getElementById('selectHutan');
            const container = document.getElementById('containerHutanManual');
            const input = document.getElementById('hutan_manual');
            const btn = document.getElementById('btnToggleHutan');

            if (container.style.display === 'none') {
                container.style.display = 'block';
                sel.value = 'manual';
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-list"></i> <span>Pilih dari Daftar</span>';
                input.focus();
            } else {
                container.style.display = 'none';
                if (sel.value === 'manual') sel.value = '';
                btn.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-pen"></i> <span>Ketik Manual</span>';
                input.value = '';
            }
        }

        function onHutanChange(sel) {
            const container = document.getElementById('containerHutanManual');
            const input = document.getElementById('hutan_manual');
            const btn = document.getElementById('btnToggleHutan');

            if (sel.value === 'manual') {
                container.style.display = 'block';
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-list"></i> <span>Pilih dari Daftar</span>';
                input.focus();
            } else {
                container.style.display = 'none';
                btn.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-pen"></i> <span>Ketik Manual</span>';
                input.value = '';
            }
        }

        // Inisialisasi Peta Leaflet
        // Default center Sulawesi Tenggara / Pomalaa region (-4.18, 121.60) atau tengah Indonesia
        const defaultLat = -4.1800;
        const defaultLng = 121.6000;
        const map = L.map('map').setView([defaultLat, defaultLng], 7);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker;

        function setTitikKejadian(lat, lng, zoomLevel = null) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }

            marker.bindPopup(`<b>Titik Kejadian Terpilih</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();

            if (zoomLevel) {
                map.flyTo([lat, lng], zoomLevel, { duration: 1.2 });
            }

            // Reverse geocoding untuk update textarea lokasi jika belum diisi atau terisi otomatis
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.display_name) {
                        const txtLokasi = document.getElementById('textareaLokasi');
                        if (txtLokasi && (!txtLokasi.value.trim() || txtLokasi.dataset.autoFilled === 'true')) {
                            txtLokasi.value = data.display_name;
                            txtLokasi.dataset.autoFilled = 'true';
                        }
                    }
                })
                .catch(err => console.log('Geocoding:', err));
        }

        // Klik pada Peta untuk menandai titik kejadian
        map.on('click', function(e) {
            setTitikKejadian(e.latlng.lat, e.latlng.lng);
            const statusEl = document.getElementById('searchStatus');
            statusEl.style.display = 'block';
            statusEl.style.background = '#f0fdf4';
            statusEl.style.borderColor = '#bbf7d0';
            statusEl.innerHTML = `<span style="color:#15803d"><i class="fas fa-check-circle"></i> Titik kejadian disetel ke koordinat: <strong>${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}</strong></span>`;
        });

        // Fitur Cari Lokasi (contoh: masyarakat ketik "pomala" -> peta pindah ke Pomala)
        function cariLokasi() {
            const query = document.getElementById('mapSearchInput').value.trim();
            if (!query) {
                alert('Silakan ketik nama lokasi atau daerah yang ingin dicari.');
                return;
            }

            const statusEl = document.getElementById('searchStatus');
            statusEl.style.display = 'block';
            statusEl.style.background = '#eff6ff';
            statusEl.style.borderColor = '#bfdbfe';
            statusEl.innerHTML = `<span style="color:#1d4ed8"><i class="fas fa-spinner fa-spin"></i> Mencari lokasi "<strong>${query}</strong>"...</span>`;

            // Prioritaskan pencarian di Indonesia
            fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const target = data[0];
                        const lat = parseFloat(target.lat);
                        const lon = parseFloat(target.lon);

                        map.flyTo([lat, lon], 13, { duration: 1.5 });
                        statusEl.style.background = '#f0fdf4';
                        statusEl.style.borderColor = '#bbf7d0';
                        statusEl.innerHTML = `<span style="color:#15803d"><i class="fas fa-check-circle"></i> Peta dipindahkan ke <strong>${target.display_name.split(',')[0]}</strong>. Silakan klik tepat pada titik kejadian.</span>`;
                    } else {
                        // Coba pencarian global jika pencarian spesifik ID tidak ketemu
                        return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                            .then(r => r.json())
                            .then(dataGlobal => {
                                if (dataGlobal && dataGlobal.length > 0) {
                                    const target = dataGlobal[0];
                                    const lat = parseFloat(target.lat);
                                    const lon = parseFloat(target.lon);
                                    map.flyTo([lat, lon], 13, { duration: 1.5 });
                                    statusEl.style.background = '#f0fdf4';
                                    statusEl.style.borderColor = '#bbf7d0';
                                    statusEl.innerHTML = `<span style="color:#15803d"><i class="fas fa-check-circle"></i> Peta dipindahkan ke <strong>${target.display_name.split(',')[0]}</strong>. Silakan klik tepat pada titik kejadian.</span>`;
                                } else {
                                    statusEl.style.background = '#fef2f2';
                                    statusEl.style.borderColor = '#fecaca';
                                    statusEl.innerHTML = `<span style="color:#dc2626"><i class="fas fa-exclamation-circle"></i> Lokasi "<strong>${query}</strong>" tidak ditemukan. Coba periksa ejaan atau nama daerah terdekat.</span>`;
                                }
                            });
                    }
                })
                .catch(err => {
                    console.error('Search error:', err);
                    statusEl.style.background = '#fef2f2';
                    statusEl.style.borderColor = '#fecaca';
                    statusEl.innerHTML = `<span style="color:#dc2626"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat mencari lokasi. Silakan periksa koneksi internet.</span>`;
                });
        }

        // Fitur Gunakan Lokasi Saat Ini (GPS Geolocation)
        function gunakanLokasiSaatIni() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung fitur deteksi lokasi (Geolocation).');
                return;
            }

            const statusEl = document.getElementById('searchStatus');
            statusEl.style.display = 'block';
            statusEl.style.background = '#eff6ff';
            statusEl.style.borderColor = '#bfdbfe';
            statusEl.innerHTML = `<span style="color:#1d4ed8"><i class="fas fa-spinner fa-spin"></i> Mendeteksi koordinat GPS lokasi Anda saat ini...</span>`;

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    setTitikKejadian(lat, lng, 16);

                    statusEl.style.background = '#f0fdf4';
                    statusEl.style.borderColor = '#bbf7d0';
                    statusEl.innerHTML = `<span style="color:#15803d"><i class="fas fa-check-circle"></i> Lokasi GPS Anda berhasil digunakan! Koordinat otomatis terisi.</span>`;
                },
                function(err) {
                    let errMsg = 'Tidak dapat mengambil lokasi Anda saat ini.';
                    if (err.code === err.PERMISSION_DENIED) {
                        errMsg = 'Izin akses lokasi ditolak oleh browser. Mohon aktifkan izin lokasi.';
                    } else if (err.code === err.POSITION_UNAVAILABLE) {
                        errMsg = 'Informasi lokasi perangkat tidak tersedia.';
                    } else if (err.code === err.TIMEOUT) {
                        errMsg = 'Permintaan deteksi lokasi habis waktu.';
                    }
                    statusEl.style.background = '#fef2f2';
                    statusEl.style.borderColor = '#fecaca';
                    statusEl.innerHTML = `<span style="color:#dc2626"><i class="fas fa-exclamation-triangle"></i> ${errMsg}</span>`;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        // Cek jika sudah ada koordinat yang terisi (misal saat validasi form gagal / kembali)
        const initLat = parseFloat(document.getElementById('latitude').value);
        const initLng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(initLat) && !isNaN(initLng)) {
            setTitikKejadian(initLat, initLng, 14);
        }
    </script>

    <?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>