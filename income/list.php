<?php
require_once '../includes/init.php';

// محتوای صفحه
ob_start();
?>

<div class="income-list-container">
    <h1>لیست درآمدها</h1>
    <!-- محتوای لیست درآمدها -->
</div>

<?php
$content = ob_get_clean();

// نمایش قالب
echo Template::getInstance()
    ->setTitle('لیست درآمدها | سیستم حسابداری')
    ->setContent($content)
    ->render();