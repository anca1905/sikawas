<?php
require_once '../../config/init.php';
requireLogin();
session_destroy();
redirect(BASE_URL . 'login.php');
