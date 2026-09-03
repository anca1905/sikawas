<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin($role = null)
{
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'login.php');
    }
    if ($role && $_SESSION['role'] !== $role) {
        $redirectMap = [
            'admin'   => BASE_URL . 'pages/admin/dashboard.php',
            'masyarakat' => BASE_URL . 'pages/masyarakat/dashboard.php',
            'polisi'  => BASE_URL . 'pages/polisi/dashboard.php',
            'pimpinan' => BASE_URL . 'pages/pimpinan/dashboard.php',
        ];
        $dest = $redirectMap[$_SESSION['role']] ?? BASE_URL . 'login.php';
        redirect($dest);
    }
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatTanggal($date)
{
    if (!$date) return '-';
    $months = [
        '',
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $parts = explode('-', $date);
    if (count($parts) < 3) return $date;
    return (int)$parts[2] . ' ' . $months[(int)$parts[1]] . ' ' . $parts[0];
}

function getStatusBadge($status)
{
    $map = [
        'Baru'        => ['class' => 'badge-new',       'icon' => 'fa-clock'],
        'Diverifikasi' => ['class' => 'badge-verified',  'icon' => 'fa-check-circle'],
        'Diproses'    => ['class' => 'badge-process',   'icon' => 'fa-spinner'],
        'Selesai'     => ['class' => 'badge-done',      'icon' => 'fa-check-double'],
        'Ditolak'     => ['class' => 'badge-rejected',  'icon' => 'fa-times-circle'],
    ];
    $info = $map[$status] ?? ['class' => 'badge-new', 'icon' => 'fa-question'];
    return '<span class="badge ' . $info['class'] . '"><i class="fas ' . $info['icon'] . '"></i> ' . $status . '</span>';
}

function uploadFoto($file, $subfolder = 'uploads')
{
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null; // 5MB

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('foto_') . '.' . strtolower($ext);
    $uploadDir = ROOT_PATH . $subfolder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return $subfolder . '/' . $filename;
    }
    return null;
}

function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
