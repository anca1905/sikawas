<?php
require 'config/init.php';
$db = getDB();
$stmt = $db->query('SELECT id_laporan, status, id_admin, id_polisi, catatan_admin FROM pengaduan');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
