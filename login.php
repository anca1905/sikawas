<?php
require_once 'config/init.php';
if (isLoggedIn()) {
    $roleMap = ['admin' => 'pages/admin/dashboard.php', 'masyarakat' => 'pages/masyarakat/dashboard.php', 'polisi' => 'pages/polisi/dashboard.php', 'pimpinan' => 'pages/pimpinan/dashboard.php'];
    if (isset($roleMap[$_SESSION['role']])) {
        redirect(BASE_URL . $roleMap[$_SESSION['role']]);
    } else {
        session_unset();
        session_destroy();
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = sanitize($_POST['role'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!in_array($role, ['masyarakat', 'admin', 'polisi', 'pimpinan'])) {
        $error = 'Pilih peran pengguna yang valid.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        $db = getDB();
        $tableMap = [
            'masyarakat' => ['masyarakat', 'id_user'],
            'admin'      => ['admin', 'id_admin'],
            'polisi'     => ['polisi_hutan', 'id_polisi'],
            'pimpinan'   => ['pimpinan', 'id_pimpinan'],
        ];
        [$table, $idCol] = $tableMap[$role];
        $stmt = $db->prepare("SELECT * FROM $table WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user[$idCol];
            $_SESSION['role']     = $role;
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];
            $dest = ['masyarakat' => 'pages/masyarakat/dashboard.php', 'admin' => 'pages/admin/dashboard.php', 'polisi' => 'pages/polisi/dashboard.php', 'pimpinan' => 'pages/pimpinan/dashboard.php'];
            redirect(BASE_URL . $dest[$role]);
        } else {
            $error = 'Username atau password salah. Pastikan peran pengguna yang dipilih sudah benar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Masuk — SIKAWAS | Sistem Informasi Pelaporan Kejahatan Lingkungan di Kawasan Hutan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body>

    <!-- Red top accent -->
    <div class="auth-top-strip"></div>

    <!-- Government header -->
    <div class="auth-govt-header">
        <div class="header-inner">
            <div class="garuda-badge"><span>🌿</span></div>
            <div class="header-text">
                <h1>Sistem Informasi Pelaporan Kejahatan Lingkungan</h1>
                <p>Kawasan Hutan — Kementerian Lingkungan Hidup dan Kehutanan</p>
            </div>
        </div>
    </div>

    <!-- Main login body -->
    <div class="auth-body-wrap">
        <div style="width:100%;max-width:460px">
            <!-- Breadcrumb info -->
            <p style="font-size:11px;color:#7F8C8D;margin-bottom:10px;text-align:center">
                <i class="fas fa-home"></i> &nbsp;<a href="index.php" style="color:#1A3A6B;text-decoration:none">Beranda</a>
                &nbsp;/&nbsp; Masuk ke Sistem
            </p>

            <div class="auth-card">
                <div class="auth-card-header">
                    <h2><i class="fas fa-lock" style="margin-right:8px"></i>Autentikasi Pengguna</h2>
                    <p>Masukkan kredensial Anda untuk mengakses sistem</p>
                </div>

                <div class="auth-card-body">
                    <?php if ($error): ?>
                        <div class="auth-alert auth-alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <!-- Role Selector -->
                        <div class="form-group">
                            <label class="form-label">Peran Pengguna</label>
                            <div class="role-selector">
                                <?php
                                $roles = [
                                    'masyarakat' => ['icon' => 'fas fa-users',      'label' => 'Masyarakat'],
                                    'admin'      => ['icon' => 'fas fa-user-shield', 'label' => 'Admin'],
                                    'polisi'     => ['icon' => 'fas fa-hard-hat',   'label' => 'Polisi Hutan'],
                                    'pimpinan'   => ['icon' => 'fas fa-user-tie',   'label' => 'Pimpinan'],
                                ];
                                foreach ($roles as $val => $r):
                                    $checked = ($_POST['role'] ?? 'masyarakat') === $val ? 'checked' : '';
                                ?>
                                    <input type="radio" name="role" id="role_<?= $val ?>" value="<?= $val ?>" class="role-option" <?= $checked ?>>
                                    <label for="role_<?= $val ?>" class="role-label">
                                        <i class="<?= $r['icon'] ?>"></i> <?= $r['label'] ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="form-group">
                            <label class="form-label" for="username">Username</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="username" name="username" class="form-control"
                                    placeholder="Masukkan username"
                                    value="<?= sanitize($_POST['username'] ?? '') ?>" required autocomplete="username">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="Masukkan password" required autocomplete="current-password">
                                <button type="button" class="toggle-pw" onclick="togglePw()">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth">
                            <i class="fas fa-sign-in-alt"></i> &nbsp;MASUK KE SISTEM
                        </button>
                    </form>
                </div>

                <div class="auth-card-footer">
                    Belum memiliki akun? &nbsp;<a href="register.php">Daftar sebagai Masyarakat</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                </div>
            </div>

            <!-- Info Box -->
            <div style="margin-top:14px;padding:10px 14px;background:#EBF5FF;border:1px solid #AED6F1;border-radius:3px;font-size:11.5px;color:#1A5276">
                <i class="fas fa-info-circle"></i>
                <strong>Informasi:</strong> Sistem ini hanya dapat diakses oleh pengguna yang telah terdaftar.
                Jika mengalami masalah login, hubungi Administrator sistem.
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="auth-footer-bar">
        &copy; <?= date('Y') ?> SIKAWAS — Sistem Informasi Pelaporan Kejahatan Lingkungan di Kawasan Hutan
        &nbsp;|&nbsp; Kementerian Lingkungan Hidup dan Kehutanan
    </div>

    <script>
        function togglePw() {
            const pw = document.getElementById('password');
            const ic = document.getElementById('eyeIcon');
            pw.type = pw.type === 'password' ? 'text' : 'password';
            ic.className = pw.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        }
    </script>
</body>

</html>