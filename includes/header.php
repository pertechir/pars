<?php
// تعیین مسیر پایه پروژه
$base_url = '/pars';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($this->title); ?></title>
    
    <!-- فونت‌ها -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/fonts.css">
    
    <!-- استایل‌های اصلی -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/sidebar.css">
    
    <!-- فونت‌آسام -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- جاوااسکریپت‌های اصلی -->
    <script src="<?php echo $base_url; ?>/assets/js/auth.js" defer></script>
</head>
<body>
    <div class="app-container">