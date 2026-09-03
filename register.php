<?php
require_once 'config/init.php';
if (isLoggedIn()) redirect(BASE_URL . 'pages/masyarakat/dashboard.php');

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize($_POST['nama'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $no_hp    = sanitize($_POST['no_hp'] ?? '');
    $alamat   = sanitize($_POST['alamat'] ?? '');

    if (!$nama || !$username || !$password) {
        $error = 'Nama, username, dan password wajib diisi.';
    } elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $db = getDB();
        $chk = $db->prepare("SELECT id_user FROM masyarakat WHERE username=?");
        $chk->execute([$username]);
        if ($chk->fetch()) {
            $error = "Username \"$username\" sudah digunakan. Pilih username lain.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO masyarakat (nama, username, password, no_hp, alamat) VALUES (?,?,?,?,?)")
                ->execute([$nama, $username, $hash, $no_hp, $alamat]);
            setFlash('success', 'Pendaftaran berhasil! Silakan login dengan akun Anda.');
            redirect(BASE_URL . 'login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Daftar Akun — SIKAWAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media(max-width:520px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="auth-top-strip"></div>

    <div class="auth-govt-header">
        <div class="header-inner">
            <div class="garuda-badge"><span>🌿</span></div>
            <div class="header-text">
                <h1>Sistem Informasi Pelaporan Kejahatan Lingkungan</h1>
                <p>Kawasan Hutan — Kementerian Lingkungan Hidup dan Kehutanan</p>
            </div>
        </div>
    </div>

    <div class="auth-body-wrap">
        <div style="width:100%;max-width:520px">
            <p style="font-size:11px;color:#7F8C8D;margin-bottom:10px;text-align:center">
                <i class="fas fa-home"></i> &nbsp;<a href="index.php" style="color:#1A3A6B;text-decoration:none">Beranda</a>
                &nbsp;/&nbsp; <a href="login.php" style="color:#1A3A6B;text-decoration:none">Masuk</a>
                &nbsp;/&nbsp; Pendaftaran
            </p>

            <div class="auth-card" style="max-width:520px">
                <div class="auth-card-header">
                    <h2><i class="fas fa-user-plus" style="margin-right:8px"></i>Pendaftaran Akun Masyarakat</h2>
                    <p>Isi formulir di bawah untuk mendaftarkan akun baru</p>
                </div>

                <div class="auth-card-body">
                    <?php if ($error): ?>
                        <div class="auth-alert auth-alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <!-- Notice -->
                    <div style="padding:9px 12px;background:#EBF5FB;border:1px solid #AED6F1;border-radius:3px;font-size:12px;color:#1A5276;margin-bottom:16px">
                        <i class="fas fa-info-circle"></i>
                        Formulir pendaftaran ini hanya untuk <strong>masyarakat umum</strong>. Akun admin, polisi hutan, dan pimpinan dibuat oleh administrator sistem.
                    </div>

                    <form method="POST" autocomplete="off">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="nama" class="form-control" value="<?= sanitize($_POST['nama'] ?? '') ?>" placeholder="Nama lengkap sesuai KTP" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">No. Handphone</label>
                                <div class="input-group">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="text" name="no_hp" class="form-control" value="<?= sanitize($_POST['no_hp'] ?? '') ?>" placeholder="08xx-xxxx-xxxx">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap</label>
                            <div class="input-group">
                                <i class="fas fa-home input-icon" style="top:11px;transform:none"></i>
                                <textarea name="alamat" class="form-control" rows="2" style="padding-left:32px" placeholder="Jl. Merdeka No. 1, Desa ..."><?= sanitize($_POST['alamat'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <hr style="border:none;border-top:1px dashed #DDD;margin:14px 0">
                        <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#7F8C8D;margin-bottom:12px">Informasi Login</p>

                        <div class="form-group">
                            <label class="form-label">Username <span style="color:red">*</span></label>
                            <div class="input-group">
                                <i class="fas fa-at input-icon"></i>
                                <input type="text" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" placeholder="Min. 4 karakter, tanpa spasi" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Password <span style="color:red">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="password" id="pw" class="form-control" placeholder="Min. 6 karakter" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Konfirmasi Password <span style="color:red">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth">
                            <i class="fas fa-user-plus"></i> &nbsp;DAFTARKAN AKUN
                        </button>
                    </form>
                </div>

                <div class="auth-card-footer">
                    Sudah memiliki akun? &nbsp;<a href="login.php">Masuk ke Sistem</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-footer-bar">
        &copy; <?= date('Y') ?> SIKAWAS — Kementerian Lingkungan Hidup dan Kehutanan
    </div>

</body>

</html>