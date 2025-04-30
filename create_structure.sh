#!/bin/bash  

# ساخت دایرکتوری‌ها  
mkdir -p assets/css  
mkdir -p assets/js  
mkdir -p config  
mkdir -p includes  
mkdir -p auth  
mkdir -p dashboard  

# ساخت فایل‌های CSS داخل assets/css  
touch assets/css/style.css  
touch assets/css/dashboard.css  
touch assets/css/auth.css  

# ساخت فایل‌های JS داخل assets/js  
touch assets/js/main.js  
touch assets/js/dashboard.js  

# فایل config/database.php  
touch config/database.php  

# فایل‌های includes  
touch includes/header.php  
touch includes/footer.php  
touch includes/sidebar.php  

# فایل‌های auth  
touch auth/login.php  
touch auth/register.php  
touch auth/logout.php  

# فایل‌های dashboard  
touch dashboard/index.php  
touch dashboard/transactions.php  
touch dashboard/reports.php  
touch dashboard/settings.php  

# فایل اصلی index.php در ریشه  
touch index.php  

echo "ساختار پروژه با موفقیت ایجاد شد."  