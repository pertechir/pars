<?php
class Sidebar {
    private $current_page;
    private $user_data;
    
    public function __construct() {
        $this->current_page = $this->getCurrentPage();
        $this->user_data = $this->getUserData();
    }
    
    private function getCurrentPage() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return basename($path);
    }
    
    private function getUserData() {
        if (isset($_SESSION['user_id'])) {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT fullname, email FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        }
        return null;
    }
    
    private function isActiveMenu($page) {
        return strpos($this->current_page, $page) !== false;
    }
    
    private function isActiveSubmenu($pages) {
        foreach ($pages as $page) {
            if ($this->isActiveMenu($page)) {
                return true;
            }
        }
        return false;
    }
    
    public function render() {
        ?>
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="/assets/images/logo.svg" alt="لوگو" class="logo">
                    <h2>سیستم حسابداری</h2>
                </div>
                <button class="sidebar-toggle">
                    <span class="toggle-icon"></span>
                </button>
            </div>

            <div class="user-profile">
                <div class="profile-image">
                    <img src="/assets/images/profile-default.png" alt="تصویر پروفایل">
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($this->user_data['fullname']); ?></h3>
                    <p><?php echo htmlspecialchars($this->user_data['email']); ?></p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <!-- داشبورد -->
                    <li class="nav-item <?php echo $this->isActiveMenu('dashboard') ? 'active' : ''; ?>">
                        <a href="/dashboard" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>داشبورد</span>
                        </a>
                    </li>

                    <!-- مدیریت درآمدها -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['income', 'add-income', 'income-categories']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>مدیریت درآمدها</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('income-list') ? 'active' : ''; ?>">
                                <a href="/income/list">لیست درآمدها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-income') ? 'active' : ''; ?>">
                                <a href="/income/add">ثبت درآمد جدید</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('income-categories') ? 'active' : ''; ?>">
                                <a href="/income/categories">دسته‌بندی درآمدها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('income-report') ? 'active' : ''; ?>">
                                <a href="/income/report">گزارش درآمدها</a>
                            </li>
                        </ul>
                    </li>

                    <!-- مدیریت هزینه‌ها -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['expense', 'add-expense', 'expense-categories']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-credit-card"></i>
                            <span>مدیریت هزینه‌ها</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('expense-list') ? 'active' : ''; ?>">
                                <a href="/expense/list">لیست هزینه‌ها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-expense') ? 'active' : ''; ?>">
                                <a href="/expense/add">ثبت هزینه جدید</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('expense-categories') ? 'active' : ''; ?>">
                                <a href="/expense/categories">دسته‌بندی هزینه‌ها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('expense-report') ? 'active' : ''; ?>">
                                <a href="/expense/report">گزارش هزینه‌ها</a>
                            </li>
                        </ul>
                    </li>

                    <!-- حساب‌های بانکی -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['accounts', 'add-account', 'account-transactions']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-university"></i>
                            <span>حساب‌های بانکی</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('account-list') ? 'active' : ''; ?>">
                                <a href="/accounts/list">لیست حساب‌ها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-account') ? 'active' : ''; ?>">
                                <a href="/accounts/add">افزودن حساب جدید</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('account-transactions') ? 'active' : ''; ?>">
                                <a href="/accounts/transactions">تراکنش‌های بانکی</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('account-transfer') ? 'active' : ''; ?>">
                                <a href="/accounts/transfer">انتقال بین حساب‌ها</a>
                            </li>
                        </ul>
                    </li>

                    <!-- مدیریت بدهی‌ها و مطالبات -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['debts', 'claims']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span>بدهی‌ها و مطالبات</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('debt-list') ? 'active' : ''; ?>">
                                <a href="/debts/list">لیست بدهی‌ها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-debt') ? 'active' : ''; ?>">
                                <a href="/debts/add">ثبت بدهی جدید</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('claim-list') ? 'active' : ''; ?>">
                                <a href="/claims/list">لیست مطالبات</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-claim') ? 'active' : ''; ?>">
                                <a href="/claims/add">ثبت مطالبه جدید</a>
                            </li>
                        </ul>
                    </li>

                    <!-- گزارشات -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['reports']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-chart-line"></i>
                            <span>گزارشات</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('balance-sheet') ? 'active' : ''; ?>">
                                <a href="/reports/balance-sheet">تراز مالی</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('profit-loss') ? 'active' : ''; ?>">
                                <a href="/reports/profit-loss">سود و زیان</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('cash-flow') ? 'active' : ''; ?>">
                                <a href="/reports/cash-flow">گردش نقدینگی</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('tax-report') ? 'active' : ''; ?>">
                                <a href="/reports/tax">گزارش مالیاتی</a>
                            </li>
                        </ul>
                    </li>

                    <!-- تنظیمات -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['settings']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-cog"></i>
                            <span>تنظیمات</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('profile-settings') ? 'active' : ''; ?>">
                                <a href="/settings/profile">تنظیمات پروفایل</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('account-settings') ? 'active' : ''; ?>">
                                <a href="/settings/account">تنظیمات حساب</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('notification-settings') ? 'active' : ''; ?>">
                                <a href="/settings/notifications">تنظیمات اعلان‌ها</a>
                            </li>
                        </ul>
                    </li>

                    <!-- خروج -->
                    <li class="nav-item logout">
                        <a href="/auth/logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>خروج</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle sidebar
                const sidebarToggle = document.querySelector('.sidebar-toggle');
                const sidebar = document.querySelector('.sidebar');
                
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed'));
                });

                // Restore sidebar state
                if (localStorage.getItem('sidebarState') === 'true') {
                    sidebar.classList.add('collapsed');
                }

                // Submenu toggle
                const submenuLinks = document.querySelectorAll('.has-submenu');
                submenuLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const submenu = link.nextElementSibling;
                        const arrow = link.querySelector('.submenu-arrow');
                        
                        submenu.style.maxHeight = submenu.style.maxHeight ? null : submenu.scrollHeight + 'px';
                        arrow.style.transform = arrow.style.transform === 'rotate(180deg)' ? '' : 'rotate(180deg)';
                    });
                });

                // Auto expand active submenu
                const activeSubmenu = document.querySelector('.nav-item.active .submenu');
                if (activeSubmenu) {
                    activeSubmenu.style.maxHeight = activeSubmenu.scrollHeight + 'px';
                    const arrow = activeSubmenu.previousElementSibling.querySelector('.submenu-arrow');
                    arrow.style.transform = 'rotate(180deg)';
                }
            });
        </script>
        <?php
    }
}
?>