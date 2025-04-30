<?php
require_once '../includes/init.php';

// محتوای صفحه
ob_start();
?>

<div class="dashboard-container">
    <h1>داشبورد</h1>
    <!-- محتوای داشبورد -->
</div>

<?php
$content = ob_get_clean();

// نمایش قالب
echo Template::getInstance()
    ->setTitle('داشبورد | سیستم حسابداری')
    ->setContent($content)
    ->render();