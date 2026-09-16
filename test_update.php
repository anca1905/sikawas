<?php
require 'config/init.php';
$db = getDB();

$id_laporan = 5;
$id_admin = 1;
$id_polisi = 1;
$catatan = 'Test verifikasi';

try {
    $db->prepare("UPDATE pengaduan SET status='Diverifikasi',id_admin=?,id_polisi=?,catatan_admin=? WHERE id_laporan=?")
       ->execute([$id_admin, $id_polisi, $catatan, $id_laporan]);
    echo "Update OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
