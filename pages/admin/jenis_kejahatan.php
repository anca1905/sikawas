<?php
require_once '../../config/init.php';
requireLogin('admin');
$db = getDB();

// CRUD
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $act    = $_POST['action'] ?? '';
    $nama   = sanitize($_POST['nama_jenis'] ?? '');
    $ket    = sanitize($_POST['keterangan'] ?? '');
    $id_j   = (int)($_POST['id_jenis'] ?? 0);
    if ($act==='tambah' && $nama) {
        $db->prepare("INSERT INTO jenis_kejahatan (nama_jenis,keterangan) VALUES (?,?)")->execute([$nama,$ket]);
        setFlash('success',"Jenis kejahatan \"$nama\" berhasil ditambahkan.");
    } elseif ($act==='edit' && $nama && $id_j) {
        $db->prepare("UPDATE jenis_kejahatan SET nama_jenis=?,keterangan=? WHERE id_jenis=?")->execute([$nama,$ket,$id_j]);
        setFlash('success',"Jenis kejahatan berhasil diperbarui.");
    } elseif ($act==='hapus' && $id_j) {
        $used = $db->prepare("SELECT COUNT(*) FROM pengaduan WHERE id_jenis=?"); $used->execute([$id_j]);
        if ($used->fetchColumn()>0) {
            setFlash('danger','Tidak dapat menghapus: jenis kejahatan ini sudah digunakan dalam laporan.');
        } else {
            $db->prepare("DELETE FROM jenis_kejahatan WHERE id_jenis=?")->execute([$id_j]);
            setFlash('success',"Jenis kejahatan berhasil dihapus.");
        }
    }
    redirect(BASE_URL . 'pages/admin/jenis_kejahatan.php');
}
$list  = $db->query("SELECT * FROM jenis_kejahatan ORDER BY nama_jenis")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Jenis Kejahatan — SIKAWAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
<style>
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.modal-overlay.show{display:flex}
.modal-box{background:#fff;border-radius:16px;width:100%;max-width:480px;box-shadow:0 30px 80px rgba(0,0,0,.3)}
.modal-head{padding:18px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between}
.modal-head h3{font-size:15px;font-weight:700}
.modal-body{padding:24px}
.modal-foot{padding:14px 24px;border-top:1px solid #e2e8f0;display:flex;gap:10px;justify-content:flex-end}
.close-btn{background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;padding:4px}
</style>
</head>
<body>
<div class="app-layout">
<?php include ROOT_PATH . 'includes/sidebar_admin.php'; ?>
<div class="main-content">
<div class="topbar-left">\s*<button class="hamburger"</button>
    <div class="topbar-title"><h1>Jenis Kejahatan</h1><p>Kelola data master jenis kejahatan lingkungan</p></div>
    <div class="topbar-actions">
        <button onclick="openAdd()" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Jenis</button>
    </div>
</div>
<div class="page-body">
<?php if($flash): ?>
<div class="alert alert-<?=$flash['type']?>"><i class="fas fa-info-circle"></i> <?=$flash['message']?></div>
<?php endif; ?>
<div class="card">
<div class="card-header"><h3><i class="fas fa-tags" style="color:#1a7a3f;margin-right:8px"></i>Daftar Jenis Kejahatan (<?=count($list)?>)</h3></div>
<div class="table-responsive">
<table class="table">
<thead><tr><th>No</th><th>Nama Jenis</th><th>Keterangan</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach($list as $i=>$j): ?>
<tr>
    <td><?=$i+1?></td>
    <td><strong><?=sanitize($j['nama_jenis'])?></strong></td>
    <td style="max-width:300px"><span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?=sanitize($j['keterangan'])?:</span></td>
    <td>
        <button onclick='openEdit(<?=$j["id_jenis"]?>,<?=json_encode(sanitize($j["nama_jenis"]))?>,<?=json_encode(sanitize($j["keterangan"]))?>)' class="btn btn-accent btn-sm"><i class="fas fa-edit"></i></button>
        <button onclick='confirmDelete(<?=$j["id_jenis"]?>,<?=json_encode(sanitize($j["nama_jenis"]))?>)' class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
    </td>
</tr>
<?php endforeach; ?>
<?php if(!count($list)): ?>
<tr><td colspan="4"><div class="empty-state"><div class="empty-icon"><i class="fas fa-tags"></i></div><h3>Belum ada data</h3></div></td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="mdAdd">
<div class="modal-box">
<div class="modal-head"><h3><i class="fas fa-plus-circle" style="color:#1a7a3f;margin-right:8px"></i>Tambah Jenis Kejahatan</h3><button class="close-btn" onclick="closeModal('mdAdd')"><i class="fas fa-times"></i></button></div>
<form method="POST">
<div class="modal-body">
<input type="hidden" name="action" value="tambah">
<div class="form-group"><label class="form-label">Nama Jenis <span style="color:red">*</span></label><input type="text" name="nama_jenis" class="form-control" placeholder="cth: Illegal Logging" required></div>
<div class="form-group"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="3" placeholder="Deskripsi jenis kejahatan..."></textarea></div>
</div>
<div class="modal-foot"><button type="button" onclick="closeModal('mdAdd')" class="btn btn-secondary">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
</form>
</div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="mdEdit">
<div class="modal-box">
<div class="modal-head"><h3><i class="fas fa-edit" style="color:#f59e0b;margin-right:8px"></i>Edit Jenis Kejahatan</h3><button class="close-btn" onclick="closeModal('mdEdit')"><i class="fas fa-times"></i></button></div>
<form method="POST">
<div class="modal-body">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="id_jenis" id="editId">
<div class="form-group"><label class="form-label">Nama Jenis <span style="color:red">*</span></label><input type="text" name="nama_jenis" id="editNama" class="form-control" required></div>
<div class="form-group"><label class="form-label">Keterangan</label><textarea name="keterangan" id="editKet" class="form-control" rows="3"></textarea></div>
</div>
<div class="modal-foot"><button type="button" onclick="closeModal('mdEdit')" class="btn btn-secondary">Batal</button><button type="submit" class="btn btn-accent"><i class="fas fa-save"></i> Perbarui</button></div>
</form>
</div>
</div>

<!-- Modal Hapus -->
<form method="POST" id="formHapus">
<input type="hidden" name="action" value="hapus">
<input type="hidden" name="id_jenis" id="hapusId">
</form>

<script>
function openAdd(){document.getElementById('mdAdd').classList.add('show');}
function openEdit(id,nama,ket){document.getElementById('editId').value=id;document.getElementById('editNama').value=nama;document.getElementById('editKet').value=ket;document.getElementById('mdEdit').classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
function confirmDelete(id,nama){if(confirm('Hapus jenis kejahatan "'+nama+'"?')){document.getElementById('hapusId').value=id;document.getElementById('formHapus').submit();}}
document.querySelectorAll('.modal-overlay').forEach(m=>m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show')}));
</script>

<?php include ROOT_PATH . 'includes/mobile_nav.php'; ?>
</body>
</html>
