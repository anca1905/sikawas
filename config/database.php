<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sikawas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;background:#fee;color:#c00;border:1px solid #c00;border-radius:8px;margin:20px;">
                <strong>Koneksi Database Gagal:</strong> ' . htmlspecialchars($e->getMessage()) . '
                <br><small>Pastikan MySQL berjalan dan database "sikawas" sudah dibuat.</small>
                </div>');
        }
    }
    return $pdo;
}
