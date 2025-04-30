<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | سیستم مدیریت' : 'سیستم مدیریت'; ?></title>

    <!-- فونت‌های وب -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- استایل‌های اصلی -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/sidebar.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- اگر در صفحه داشبورد هستیم -->
    <?php if(strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false): ?>
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Sweetalert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- استایل و اسکریپت داشبورد -->
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <?php endif; ?>

    <!-- اسکریپت‌های اصلی -->
    <script src="<?php echo BASE_URL; ?>/assets/js/sidebar.js" defer></script>
    
    <!-- اگر در صفحه داشبورد هستیم -->
    <?php if(strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false): ?>
        <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js" defer></script>
    <?php endif; ?>
</head>
    <style>
        /* استایل‌های پایه */
        :root {
            --primary-color: #4a6cf7;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }

        /* استایل‌های عمومی */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #3955c4;
        }
    </style>
</head>
<body>