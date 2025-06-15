<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auto_check.php'; 
$page = $_GET['page'] ?? null;

if (empty($page)) {
    $page = is_logged_in() ? 'dashboard' : 'login'; 
}

$page = preg_replace('/[^a-zA-Z0-9_]/', '', $page);

$allowed_pages = ['login', 'register', 'dashboard','create','exhibits','edit', 'delete', 'logout','view','profile']; 
if (!in_array($page, $allowed_pages)) {
    $page = 'login';
}

if (!is_logged_in() && !in_array($page, ['login', 'register'])) {
    $_SESSION['error'] = 'Bu sayfaya erişmek için lütfen giriş yapın.'; 
    header("Location: index.php?page=login");
    exit();
}

$page_file = __DIR__ . '/pages/' . $page . '.php';

if (file_exists($page_file)) {
    
    require_once $page_file; 
} else {
    
    error_log("Sayfa dosyası bulunamadı: " . $page_file); 
    $_SESSION['error'] = 'Aradığınız sayfa bulunamadı.';
    header("Location: index.php?page=login");
    exit();
}


?>
