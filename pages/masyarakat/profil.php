<?php
require_once '../../config/init.php';
requireLogin('masyarakat');
$db  = getDB();
$uid = $_SESSION['user_id'];

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = sanitize($_POST['nama'] ?? '');
    $no_hp   = sanitize($_POST['no_hp'] ?? '');
    $alamat  = sanitize($_POST['alamat'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$nama) {
        $error = 'Nama wajib diisi.';
    } elseif ($pass && strlen($pass) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($pass && $pass !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $db->prepare("UPDATE masyarakat SET nama=?,no_hp=?,alamat=?,password=? WHERE id_user=?")->execute([$nama, $no_hp, $alamat, $hash, $uid]);
        } else {
            $db->prepare("UPDATE masyarakat SET nama=?,no_hp=?,alamat=? WHERE id_user=?")->execute([$nama, $no_hp, $alamat, $uid]);
        }
        $_SESSION['nama'] = $nama;
        $success = 'Profil berhasil diperbarui.';
    }
}
$user = $db->prepare("SELECT * FROM masyarakat WHERE id_user=?");
$user->execute([$uid]);
$user = $user->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Profil Saya — SIKAWAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include ROOT_PATH . 'includes/sidebar_masyarakat.php'; ?>
        <div class="main-content">
            <div class="topbar">
    
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <div class="topbar-left">
                <div class="topbar-title">
                    <h1>Profil Saya</h1>
                    <p>Kelola informasi akun Anda</p>
                </div>
            </div>
            <div class="page-body">
                <div style="display:grid;grid-template-columns:300px 1fr;gap:24px">
                    <!-- Avatar Card -->
                    <div>
                        <div class="card" style="text-align:center;padding:32px 24px">
                            <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#1a7a3f,#2ea055);color:#fff;font-size:36px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 8px 24px rgba(26,122,63,.3)"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
                            <h3 style="font-size:18px;font-weight:700"><?= sanitize($user['nama']) ?></h3>
                            <p style="font-size:13px;color:#64748b;margin-top:4px">@<?= sanitize($user['username']) ?></p>
                            <div style="margin-top:12px"><span class="badge badge-done"><i class="fas fa-users"></i> Masyarakat</span></div>
                            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;font-size:12px;color:#94a3b8">
                                <p>Bergabung: <?= formatTanggal($user['created_at']) ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Form -->
                    <div>
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-edit" style="color:#1a7a3f;margin-right:8px"></i>Edit Profil</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
                                <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
                                <form method="POST">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                                            <div class="input-group"><i class="fas fa-user input-icon"></i><input type="text" name="nama" class="form-control" value="<?= sanitize($user['nama']) ?>" required></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">No. HP</label>
                                            <div class="input-group"><i class="fas fa-phone input-icon"></i><input type="text" name="no_hp" class="form-control" value="<?= sanitize($user['no_hp'] ?? '') ?>" placeholder="08xx-xxxx-xxxx"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Alamat</label>
                                        <div class="input-group"><i class="fas fa-home input-icon" style="top:12px;transform:none"></i><textarea name="alamat" class="form-control" rows="3" style="padding-left:38px"><?= sanitize($user['alamat'] ?? '') ?></textarea></div>
                                    </div>
                                    <hr style="border:none;border-top:1px solid #f1f5f9;margin:20px 0">
                                    <h4 style="font-size:14px;font-weight:700;margin-bottom:16px">Ganti Password (Opsional)</h4>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Password Baru</label>
                                            <div class="input-group"><i class="fas fa-lock input-icon"></i><input type="password" name="password" class="form-control" placeholder="Min. 6 karakter"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Konfirmasi Password</label>
                                            <div class="input-group"><i class="fas fa-lock input-icon"></i><input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru"></div>
                                        </div>
                                    </div>
                                    <div style="text-align:right"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>