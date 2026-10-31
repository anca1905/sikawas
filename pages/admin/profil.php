<?php
require_once '../../config/init.php';
requireLogin('admin');
$db  = getDB();
$uid = (int)$_SESSION['user_id'];

// Ambil data admin saat ini
$stmt = $db->prepare("SELECT * FROM admin WHERE id_admin = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) {
    redirect(BASE_URL . 'logout.php');
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = sanitize($_POST['nama'] ?? '');
    $username    = sanitize($_POST['username'] ?? '');
    $no_hp       = sanitize($_POST['no_hp'] ?? '');
    $pass        = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $hapusFoto   = isset($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1';
    $clean_no_hp = preg_replace('/[^0-9]/', '', $no_hp);

    if (!$nama) {
        $error = 'Nama lengkap wajib diisi.';
    } elseif (!$username) {
        $error = 'Username wajib diisi.';
    } elseif ($no_hp && strlen($clean_no_hp) < 12) {
        $error = 'No. Handphone minimal 12 digit angka.';
    } elseif ($pass && strlen($pass) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($pass && $pass !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek username unik di tabel admin
        $chk = $db->prepare("SELECT id_admin FROM admin WHERE username = ? AND id_admin != ?");
        $chk->execute([$username, $uid]);
        if ($chk->fetch()) {
            $error = 'Username "' . $username . '" sudah digunakan oleh akun lain.';
        } else {
            $save_no_hp = $clean_no_hp ?: $no_hp;
            $fotoPath = $user['foto'];

            // Hapus foto jika dicentang
            if ($hapusFoto) {
                hapusFotoProfil($user['foto']);
                $fotoPath = null;
                $_SESSION['foto'] = null;
            }

            // Upload foto baru jika ada
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = uploadFotoProfil($_FILES['foto'], 'admin', $uid, $user['foto']);
                if ($uploadResult['success']) {
                    $fotoPath = $uploadResult['path'];
                } else {
                    $error = $uploadResult['error'];
                }
            }

            if (!$error) {
                if ($pass) {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $db->prepare("UPDATE admin SET nama=?, username=?, no_hp=?, password=?, foto=? WHERE id_admin=?")
                        ->execute([$nama, $username, $save_no_hp, $hash, $fotoPath, $uid]);
                } else {
                    $db->prepare("UPDATE admin SET nama=?, username=?, no_hp=?, foto=? WHERE id_admin=?")
                        ->execute([$nama, $username, $save_no_hp, $fotoPath, $uid]);
                }

                $_SESSION['nama']     = $nama;
                $_SESSION['username'] = $username;
                $_SESSION['foto']     = $fotoPath;
                $success = 'Profil Administrator berhasil diperbarui.';

                // Refresh data user
                $stmt = $db->prepare("SELECT * FROM admin WHERE id_admin = ?");
                $stmt->execute([$uid]);
                $user = $stmt->fetch();
            }
        }
    }
}

// Statistik Admin
$totalMasuk = (int)$db->query("SELECT COUNT(*) FROM pengaduan")->fetchColumn();
$stmtVerif = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_admin = ?");
$stmtVerif->execute([$uid]);
$countVerif = (int)$stmtVerif->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Profil Administrator — SIKAWAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .profil-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 820px) {
            .profil-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_admin.php'; ?>
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-left">
                    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div class="topbar-title">
                        <h1>Profil Administrator</h1>
                        <p>Kelola data akun, foto, dan keamanan sistem</p>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="profil-grid">
                    <!-- Avatar Card -->
                    <div>
                        <div class="card" style="text-align:center;padding:28px 20px">
                            <div class="profile-photo-wrapper">
                                <?php if (!empty($user['foto']) && file_exists(ROOT_PATH . $user['foto'])): ?>
                                    <img id="cardAvatarImg" src="<?= BASE_URL . sanitize($user['foto']) ?>" class="profile-photo-img" alt="Foto Profil">
                                    <div id="cardAvatarPlaceholder" class="profile-photo-initial" style="background:linear-gradient(135deg,#1a7a3f,#2ea055);display:none">
                                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                    </div>
                                <?php else: ?>
                                    <img id="cardAvatarImg" src="" class="profile-photo-img" alt="Foto Profil" style="display:none">
                                    <div id="cardAvatarPlaceholder" class="profile-photo-initial" style="background:linear-gradient(135deg,#1a7a3f,#2ea055)">
                                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <button type="button" class="profile-photo-badge" onclick="document.getElementById('fotoInput').click()" title="Unggah / Ganti Foto">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>

                            <h3 style="font-size:16px;font-weight:700"><?= sanitize($user['nama']) ?></h3>
                            <p style="font-size:13px;color:#64748b;margin-top:4px">@<?= sanitize($user['username']) ?></p>
                            <div style="margin-top:12px">
                                <span class="badge" style="background:#1a7a3f;color:#fff"><i class="fas fa-user-shield"></i> Administrator</span>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;text-align:center">
                                <div style="background:#f8fafc;padding:10px 8px;border-radius:10px;border:1px solid #e2e8f0">
                                    <div style="font-size:18px;font-weight:800;color:#1a7a3f"><?= $totalMasuk ?></div>
                                    <div style="font-size:11px;color:#64748b;margin-top:2px">Laporan Masuk</div>
                                </div>
                                <div style="background:#f8fafc;padding:10px 8px;border-radius:10px;border:1px solid #e2e8f0">
                                    <div style="font-size:18px;font-weight:800;color:#2563eb"><?= $countVerif ?></div>
                                    <div style="font-size:11px;color:#64748b;margin-top:2px">Diverifikasi Anda</div>
                                </div>
                            </div>

                            <div style="margin-top:16px;padding-top:14px;border-top:1px solid #f1f5f9;font-size:12px;color:#94a3b8">
                                <p><i class="far fa-calendar-alt"></i> Bergabung: <?= formatTanggal($user['created_at']) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div>
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-edit" style="color:#1a7a3f;margin-right:8px"></i>Edit Data Administrator</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                                <?php endif; ?>
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <!-- Foto Profil Upload Section -->
                                    <div class="form-group" style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0">
                                        <label class="form-label" style="font-weight:700;margin-bottom:8px">Foto Profil</label>
                                        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                                            <div style="position:relative">
                                                <div id="previewContainer" style="width:72px;height:72px;border-radius:50%;overflow:hidden;border:2px solid #cbd5e1;background:#fff;display:flex;align-items:center;justify-content:center">
                                                    <?php if (!empty($user['foto']) && file_exists(ROOT_PATH . $user['foto'])): ?>
                                                        <img id="avatarPreview" src="<?= BASE_URL . sanitize($user['foto']) ?>" style="width:100%;height:100%;object-fit:cover">
                                                        <div id="avatarPlaceholder" style="width:100%;height:100%;display:none;align-items:center;justify-content:center;background:linear-gradient(135deg,#1a7a3f,#2ea055);color:#fff;font-size:24px;font-weight:700">
                                                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div id="avatarPlaceholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1a7a3f,#2ea055);color:#fff;font-size:24px;font-weight:700">
                                                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                                        </div>
                                                        <img id="avatarPreview" src="" style="width:100%;height:100%;object-fit:cover;display:none">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div style="flex:1;min-width:200px">
                                                <input type="file" name="foto" id="fotoInput" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none" onchange="previewAvatar(this)">
                                                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('fotoInput').click()">
                                                        <i class="fas fa-camera"></i> Unggah / Ganti Foto
                                                    </button>
                                                    <?php if (!empty($user['foto'])): ?>
                                                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:#dc2626;cursor:pointer;margin:0">
                                                            <input type="checkbox" name="hapus_foto" value="1" id="hapusFotoCheckbox" onchange="toggleHapusFoto(this)">
                                                            <i class="fas fa-trash-alt"></i> Hapus Foto
                                                        </label>
                                                    <?php endif; ?>
                                                </div>
                                                <div id="fileChosenText" style="font-size:11.5px;color:#64748b;margin-top:6px">
                                                    Format: JPG, JPEG, PNG, WEBP, GIF (Maksimal 5 MB)
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                                            <div class="input-group">
                                                <i class="fas fa-user input-icon"></i>
                                                <input type="text" name="nama" class="form-control" value="<?= sanitize($user['nama']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Username <span style="color:red">*</span></label>
                                            <div class="input-group">
                                                <i class="fas fa-at input-icon"></i>
                                                <input type="text" name="username" class="form-control" value="<?= sanitize($user['username']) ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">No. Handphone <small style="color:#64748b">(Opsional, min. 12 digit)</small></label>
                                        <div class="input-group">
                                            <i class="fas fa-phone input-icon"></i>
                                            <input type="tel" name="no_hp" class="form-control" value="<?= sanitize($user['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        </div>
                                    </div>

                                    <hr style="border:none;border-top:1px solid #f1f5f9;margin:18px 0">
                                    <h4 style="font-size:14px;font-weight:700;margin-bottom:14px">Ganti Password (Opsional)</h4>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Password Baru</label>
                                            <div class="input-group">
                                                <i class="fas fa-lock input-icon"></i>
                                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Konfirmasi Password</label>
                                            <div class="input-group">
                                                <i class="fas fa-lock input-icon"></i>
                                                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru">
                                            </div>
                                        </div>
                                    </div>

                                    <div style="text-align:right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file foto terlalu besar! Maksimal 5 MB.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    const placeholder = document.getElementById('avatarPlaceholder');
                    const cardImg = document.getElementById('cardAvatarImg');
                    const cardPlaceholder = document.getElementById('cardAvatarPlaceholder');

                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    if (cardImg) {
                        cardImg.src = e.target.result;
                        cardImg.style.display = 'block';
                    }
                    if (cardPlaceholder) {
                        cardPlaceholder.style.display = 'none';
                    }

                    document.getElementById('fileChosenText').innerHTML = '<span style="color:#16a34a;font-weight:600"><i class="fas fa-check-circle"></i> File baru dipilih: ' + file.name + '</span>';
                    const hapusCb = document.getElementById('hapusFotoCheckbox');
                    if (hapusCb) hapusCb.checked = false;
                };
                reader.readAsDataURL(file);
            }
        }

        function toggleHapusFoto(cb) {
            const preview = document.getElementById('avatarPreview');
            const placeholder = document.getElementById('avatarPlaceholder');
            const cardImg = document.getElementById('cardAvatarImg');
            const cardPlaceholder = document.getElementById('cardAvatarPlaceholder');
            const fileText = document.getElementById('fileChosenText');

            if (cb.checked) {
                document.getElementById('fotoInput').value = '';
                fileText.innerHTML = '<span style="color:#ef4444;font-weight:600"><i class="fas fa-trash-alt"></i> Foto profil akan dihapus saat disimpan.</span>';
                if (preview) preview.style.display = 'none';
                if (placeholder) placeholder.style.display = 'flex';
                if (cardImg) cardImg.style.display = 'none';
                if (cardPlaceholder) cardPlaceholder.style.display = 'flex';
            } else {
                fileText.innerHTML = 'Format: JPG, JPEG, PNG, WEBP, GIF (Maksimal 5 MB)';
                if (preview) preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
                if (cardImg) cardImg.style.display = 'block';
                if (cardPlaceholder) cardPlaceholder.style.display = 'none';
            }
        }
    </script>

    <?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>
