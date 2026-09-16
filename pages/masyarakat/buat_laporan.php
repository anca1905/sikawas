<?php
require_once '../../config/init.php';
requireLogin('masyarakat');
$db  = getDB();
$uid = $_SESSION['user_id'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = sanitize($_POST['tanggal_kejadian'] ?? '');
    $lokasi  = sanitize($_POST['lokasi'] ?? '');
    $latitude  = sanitize($_POST['latitude'] ?? '');
    $longitude = sanitize($_POST['longitude'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $id_jenis  = (int)($_POST['id_jenis'] ?? 0);
    $id_hutan  = (int)($_POST['id_hutan'] ?? 0);

    if (!$tanggal || !$lokasi || !$deskripsi || !$id_jenis || !$id_hutan) {
        $error = 'Semua field wajib harus diisi.';
    } else {
        $fotoPath = uploadFoto($_FILES['bukti_foto'] ?? null, 'uploads');
        $db->prepare("INSERT INTO pengaduan (tanggal_kejadian,lokasi,latitude,longitude,deskripsi,bukti_foto,status,id_user,id_jenis,id_hutan) VALUES (?,?,?,?,?,?,?,?,?,?)")->execute([$tanggal, $lokasi, $latitude, $longitude, $deskripsi, $fotoPath, 'Baru', $uid, $id_jenis, $id_hutan]);
        setFlash('success', 'Laporan berhasil dikirim! Kami akan segera memverifikasi laporan Anda.');
        redirect(BASE_URL . 'pages/masyarakat/riwayat_laporan.php');
    }
}

$jenisList  = $db->query("SELECT * FROM jenis_kejahatan ORDER BY nama_jenis")->fetchAll();
$hutanList  = $db->query("SELECT * FROM jenis_kawasan_hutan ORDER BY nama_hutan")->fetchAll();
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
                        <!-- Kiri -->
                        <div>
                            <div class="card" style="margin-bottom:16px">
                                <div class="card-header">
                                    <h3><i class="fas fa-info-circle" style="color:#1a7a3f;margin-right:8px"></i>Informasi Kejadian</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="form-label">Jenis Kejahatan <span class="required">*</span></label>
                                        <div class="input-group"><i class="fas fa-tag input-icon"></i>
                                            <select name="id_jenis" class="form-select" required style="padding-left:38px">
                                                <option value="">-- Pilih Jenis Kejahatan --</option>
                                                <?php foreach ($jenisList as $j): ?>
                                                    <option value="<?= $j['id_jenis'] ?>" <?= ($_POST['id_jenis'] ?? '') == $j['id_jenis'] ? 'selected' : '' ?>><?= sanitize($j['nama_jenis']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Kawasan Hutan <span class="required">*</span></label>
                                        <div class="input-group"><i class="fas fa-map input-icon"></i>
                                            <select name="id_hutan" class="form-select" required style="padding-left:38px">
                                                <option value="">-- Pilih Kawasan Hutan --</option>
                                                <?php foreach ($hutanList as $h): ?>
                                                    <option value="<?= $h['id_hutan'] ?>" <?= ($_POST['id_hutan'] ?? '') == $h['id_hutan'] ? 'selected' : '' ?>><?= sanitize($h['nama_hutan']) ?> — <?= sanitize($h['lokasi_hutan']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Tanggal Kejadian <span class="required">*</span></label>
                                        <div class="input-group"><i class="fas fa-calendar input-icon"></i>
                                            <input type="date" name="tanggal_kejadian" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= sanitize($_POST['tanggal_kejadian'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Lokasi Kejadian (Detail) <span class="required">*</span></label>
                                        <div class="input-group"><i class="fas fa-map-marker-alt input-icon" style="top:12px;transform:none"></i>
                                            <textarea name="lokasi" class="form-control" rows="3" style="padding-left:38px" placeholder="cth: Blok C Sektor 3, dekat sungai besar..." required><?= sanitize($_POST['lokasi'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Deskripsi Kejadian <span class="required">*</span></label>
                                        <textarea name="deskripsi" class="form-control" rows="5" placeholder="Ceritakan secara detail kejadian yang Anda temukan..." required><?= sanitize($_POST['deskripsi'] ?? '') ?></textarea>
                                        <div class="form-hint">Semakin detail deskripsi, semakin mudah bagi petugas untuk menindaklanjuti.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kanan -->
                        <div>
                            <div class="card" style="margin-bottom:16px">
                                <div class="card-header">
                                    <h3><i class="fas fa-map-marked-alt" style="color:#1a7a3f;margin-right:8px"></i>Peta Lokasi</h3>
                                    <span style="font-size:11px;color:#64748b">Pilih Lokasi</span>
                                </div>
                                <div class="card-body" style="padding:0">
                                    <div id="map" style="height: 300px; width: 100%; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; z-index: 1;"></div>
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">
                                    <div style="padding: 10px; font-size: 12px; color: #64748b; background: #f8fafc; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 1px solid #e2e8f0;">
                                        <i class="fas fa-info-circle"></i> Klik pada peta untuk menandai lokasi kejadian.
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
    <script>
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
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Inisialisasi Peta
        // Set default ke tengah Indonesia
        const map = L.map('map').setView([-0.789275, 113.921327], 5);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker;

        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }

            // Reverse geocoding optional to auto-fill Lokasi textarea
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if(data && data.display_name) {
                        const textareaLokasi = document.querySelector('textarea[name="lokasi"]');
                        if(textareaLokasi) {
                            textareaLokasi.value = data.display_name;
                        }
                    }
                })
                .catch(err => console.error("Geocoding error: ", err));
        });
    </script>

    <?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>