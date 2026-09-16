<?php
require_once '../../config/init.php';
requireLogin('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act   = $_POST['action'] ?? '';
    $nama  = sanitize($_POST['nama'] ?? '');
    $user  = sanitize($_POST['username'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $id_p  = (int)($_POST['id_polisi'] ?? 0);
    if ($act === 'tambah' && $nama && $user && $pass) {
        $chk = $db->prepare("SELECT id_polisi FROM polisi_hutan WHERE username=?");
        $chk->execute([$user]);
        if ($chk->fetch()) {
            setFlash('danger', "Username \"$user\" sudah digunakan.");
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO polisi_hutan (nama,username,password) VALUES (?,?,?)")->execute([$nama, $user, $hash]);
            setFlash('success', "Polisi Hutan \"$nama\" berhasil ditambahkan.");
        }
    } elseif ($act === 'edit' && $nama && $user && $id_p) {
        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $db->prepare("UPDATE polisi_hutan SET nama=?,username=?,password=? WHERE id_polisi=?")->execute([$nama, $user, $hash, $id_p]);
        } else {
            $db->prepare("UPDATE polisi_hutan SET nama=?,username=? WHERE id_polisi=?")->execute([$nama, $user, $id_p]);
        }
        setFlash('success', "Data polisi hutan berhasil diperbarui.");
    } elseif ($act === 'hapus' && $id_p) {
        $db->prepare("UPDATE pengaduan SET id_polisi=NULL WHERE id_polisi=?")->execute([$id_p]);
        $db->prepare("DELETE FROM polisi_hutan WHERE id_polisi=?")->execute([$id_p]);
        setFlash('success', "Akun polisi hutan berhasil dihapus.");
    }
    redirect(BASE_URL . 'pages/admin/kelola_polisi.php');
}
$list  = $db->query("SELECT ph.*, (SELECT COUNT(*) FROM pengaduan WHERE id_polisi=ph.id_polisi) as jml_kasus FROM polisi_hutan ph ORDER BY ph.nama")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Kelola Polisi Hutan — SIKAWAS</title>
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
            max-width: 460px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .3)
        }

        .modal-head {
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .modal-head h3 {
            font-size: 15px;
            font-weight: 700
        }

        .modal-body {
            padding: 24px
        }

        .modal-foot {
            padding: 14px 24px;
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
            color: #94a3b8;
            padding: 4px
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
            <h1>Kelola Polisi Hutan</h1>
                    <p>Manajemen akun petugas Polisi Hutan</p>
        </div>
    </div>
    <div class="topbar-actions">
        <button onclick="openAdd()" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Polisi</button>
    </div>
</div>
<div class="page-body">
                <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-hard-hat" style="color:#1a7a3f;margin-right:8px"></i>Daftar Polisi Hutan (<?= count($list) ?>)</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Total Kasus</th>
                                    <th>Bergabung</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($list as $i => $p): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:10px">
                                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#60a5fa);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px"><?= strtoupper(substr($p['nama'], 0, 1)) ?></div>
                                                <strong><?= sanitize($p['nama']) ?></strong>
                                            </div>
                                        </td>
                                        <td><code style="background:#f1f5f9;padding:3px 8px;border-radius:5px;font-size:12px"><?= sanitize($p['username']) ?></code></td>
                                        <td><span style="font-weight:700;color:#1a7a3f"><?= $p['jml_kasus'] ?></span> kasus</td>
                                        <td><?= formatTanggal($p['created_at']) ?></td>
                                        <td>
                                            <button onclick='openEdit(<?= $p["id_polisi"] ?>,<?= json_encode(sanitize($p["nama"])) ?>,<?= json_encode(sanitize($p["username"])) ?>)' class="btn btn-accent btn-sm"><i class="fas fa-edit"></i></button>
                                            <button onclick='confirmDelete(<?= $p["id_polisi"] ?>,<?= json_encode(sanitize($p["nama"])) ?>)' class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="mdAdd">
            <div class="modal-box">
                <div class="modal-head">
                    <h3><i class="fas fa-user-plus" style="color:#1a7a3f;margin-right:8px"></i>Tambah Polisi Hutan</h3><button class="close-btn" onclick="closeModal('mdAdd')"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="tambah">
                        <div class="form-group"><label class="form-label">Nama Lengkap <span style="color:red">*</span></label><input type="text" name="nama" class="form-control" placeholder="Nama petugas" required></div>
                        <div class="form-group"><label class="form-label">Username <span style="color:red">*</span></label><input type="text" name="username" class="form-control" placeholder="username login" required></div>
                        <div class="form-group"><label class="form-label">Password <span style="color:red">*</span></label><input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required></div>
                    </div>
                    <div class="modal-foot"><button type="button" onclick="closeModal('mdAdd')" class="btn btn-secondary">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="mdEdit">
            <div class="modal-box">
                <div class="modal-head">
                    <h3><i class="fas fa-edit" style="color:#f59e0b;margin-right:8px"></i>Edit Polisi Hutan</h3><button class="close-btn" onclick="closeModal('mdEdit')"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_polisi" id="editId">
                        <div class="form-group"><label class="form-label">Nama Lengkap <span style="color:red">*</span></label><input type="text" name="nama" id="editNama" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Username <span style="color:red">*</span></label><input type="text" name="username" id="editUser" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Password Baru</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah"></div>
                    </div>
                    <div class="modal-foot"><button type="button" onclick="closeModal('mdEdit')" class="btn btn-secondary">Batal</button><button type="submit" class="btn btn-accent"><i class="fas fa-save"></i> Perbarui</button></div>
                </form>
            </div>
        </div>

        <form method="POST" id="formHapus"><input type="hidden" name="action" value="hapus"><input type="hidden" name="id_polisi" id="hapusId"></form>
        <script>
            function openAdd() {
                document.getElementById('mdAdd').classList.add('show');
            }

            function openEdit(id, nama, user) {
                document.getElementById('editId').value = id;
                document.getElementById('editNama').value = nama;
                document.getElementById('editUser').value = user;
                document.getElementById('mdEdit').classList.add('show');
            }

            function closeModal(id) {
                document.getElementById(id).classList.remove('show');
            }

            function confirmDelete(id, nama) {
                if (confirm('Hapus akun polisi hutan "' + nama + '"?')) {
                    document.getElementById('hapusId').value = id;
                    document.getElementById('formHapus').submit();
                }
            }
            document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('show')
            }));
        </script>

<?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>

</html>