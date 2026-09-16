<?php
$content = file_get_contents('pages/admin/kelola_laporan.php');
$pattern = '/(<div class="main-content">\s*)<div class="topbar(?:-left)?">.*?<div class="topbar-title">(.*?)<\/div>.*?<div class="page-body">/is';
preg_match($pattern, $content, $m);
print_r(array_keys($m));
