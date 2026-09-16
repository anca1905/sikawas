<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('ROOT_PATH', dirname(__DIR__) . '/');
define('BASE_URL', '/');

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'config/functions.php';
