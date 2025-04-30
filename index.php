<?php
session_start();
require_once 'config/database.php';

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم حسابداری</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <nav class="nav-container">
            <div class="logo">
                <h1>سیستم حسابداری</h1>
            </div>
            <div class="nav-links">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard/">داشبورد</a>
                    <a href="auth/logout.php">خروج</a>
                <?php else: ?>
                    <a href="auth/login.php">ورود</a>
                    <a href="auth/register.php">ثبت نام</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="landing-main">
        <section class="hero-section">
            <h2>مدیریت مالی هوشمند کسب و کار شما</h2>
            <p>با سیستم حسابداری ما، مدیریت مالی کسب و کار خود را آسان کنید</p>
            <div class="cta-buttons">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="auth/register.php" class="btn btn-primary">شروع کنید</a>
                    <a href="auth/login.php" class="btn btn-secondary">ورود به حساب</a>
                <?php else: ?>
                    <a href="dashboard/" class="btn btn-primary">ورود به داشبورد</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="features">
            <h3>ویژگی های سیستم</h3>
            <div class="features-grid">
                <div class="feature-card">
                    <h4>مدیریت تراکنش ها</h4>
                    <p>ثبت و پیگیری تمام تراکنش های مالی</p>
                </div>
                <div class="feature-card">
                    <h4>گزارش های مالی</h4>
                    <p>تهیه گزارش های متنوع از وضعیت مالی</p>
                </div>
                <div class="feature-card">
                    <h4>مدیریت حساب ها</h4>
                    <p>کنترل و مدیریت حساب های بانکی</p>
                </div>
                <div class="feature-card">
                    <h4>صدور فاکتور</h4>
                    <p>ایجاد و مدیریت فاکتورهای فروش</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>درباره ما</h4>
                <p>سیستم حسابداری ما راهکاری جامع برای مدیریت مالی کسب و کارها</p>
            </div>
            <div class="footer-section">
                <h4>تماس با ما</h4>
                <p>ایمیل: info@example.com</p>
                <p>تلفن: 123-456-789</p>
            </div>
            <div class="footer-section">
                <h4>لینک های مفید</h4>
                <ul>
                    <li><a href="#">راهنما</a></li>
                    <li><a href="#">سوالات متداول</a></li>
                    <li><a href="#">شرایط استفاده</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 تمامی حقوق محفوظ است</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>