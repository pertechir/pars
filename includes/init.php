<?php
session_start();
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/pars');

// تنظیمات منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// فانکشن اتولود برای کلاس‌ها
spl_autoload_register(function ($class) {
    $class_path = BASE_PATH . '/classes/' . $class . '.php';
    if (file_exists($class_path)) {
        require_once $class_path;
    }
});

// چک کردن لاگین کاربر
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /auth/login.php');
        exit;
    }
}

// مسیرهای مستثنی از چک کردن لاگین
$public_paths = ['/auth/login.php', '/auth/register.php', '/auth/forgot-password.php'];
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!in_array($current_path, $public_paths)) {
    checkAuth();
}