<?php
class Sidebar {
    private $current_page;
    private $user_data;
    private $notifications;
    private $favorite_menus;
    
    public function __construct() {
        $this->current_page = $this->getCurrentPage();
        $this->user_data = $this->getUserData();
        $this->notifications = $this->getNotifications();
        $this->favorite_menus = $this->getFavoriteMenus();
    }
    
    private function getCurrentPage() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return str_replace('/pars/', '', $path);
    }
    
    private function getUserData() {
        if (isset($_SESSION['user_id'])) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT 
                        u.id,
                        u.fullname,
                        u.email,
                        u.status,
                        u.last_login,
                        r.name as role_name
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE u.id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error fetching user data: " . $e->getMessage());
                return null;
            }
        }
        return null;
    }
    
    private function getNotifications() {
        if (!isset($_SESSION['user_id'])) return [];
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT 
                    id,
                    type,
                    title,
                    message,
                    created_at,
                    is_read
                FROM notifications
                WHERE user_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching notifications: " . $e->getMessage());
            return [];
        }
    }
    
    private function getFavoriteMenus() {
        if (!isset($_SESSION['user_id'])) return [];
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT 
                    id,
                    menu_name,
                    menu_url,
                    icon
                FROM favorite_menus
                WHERE user_id = ?
                ORDER BY sort_order
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching favorite menus: " . $e->getMessage());
            return [];
        }
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
    
    private function getUnreadNotificationsCount() {
        return count(array_filter($this->notifications, function($n) {
            return !$n['is_read'];
        }));
    }
    
    private function formatDate($date) {
        if (!$date) return '';
        $datetime = new DateTime($date);
        $now = new DateTime();
        $diff = $now->diff($datetime);
        
        if ($diff->days == 0) {
            if ($diff->h == 0) {
                if ($diff->i == 0) {
                    return 'چند لحظه پیش';
                }
                return $diff->i . ' دقیقه پیش';
            }
            return $diff->h . ' ساعت پیش';
        }
        if ($diff->days < 7) {
            return $diff->days . ' روز پیش';
        }
        return date('Y/m/d H:i', strtotime($date));
    }
    
    public function render() {
        $base_url = '/pars';
        ?>
        <div class="sidebar">
            <!-- هدر سایدبار -->
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="<?php echo $base_url; ?>/assets/images/logo.svg" alt="لوگو" class="logo">
                    <h2>سیستم حسابداری</h2>
                </div>
                <button class="sidebar-toggle">
                    <span class="toggle-icon"></span>
                </button>
            </div>

            <!-- پروفایل کاربر -->
            <?php if ($this->user_data): ?>
            <div class="user-profile">
                <div class="profile-image">
                    <img src="<?php echo $base_url; ?>/assets/images/profile-default.png" alt="تصویر پروفایل">
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($this->user_data['fullname']); ?></h3>
                    <p><?php echo htmlspecialchars($this->user_data['role_name']); ?></p>
                    <span class="last-login">
                        آخرین ورود: <?php echo $this->formatDate($this->user_data['last_login']); ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- منوی اصلی -->
            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <!-- داشبورد -->
                    <li class="nav-item <?php echo $this->isActiveMenu('dashboard') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/dashboard" class="nav-link">
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
                                <a href="<?php echo $base_url; ?>/income/list">لیست درآمدها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-income') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/income/add">ثبت درآمد جدید</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('income-categories') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/income/categories">دسته‌بندی درآمدها</a>
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
                                <a href="<?php echo $base_url; ?>/expense/list">لیست هزینه‌ها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-expense') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/expense/add">ثبت هزینه جدید</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('expense-categories') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/expense/categories">دسته‌بندی هزینه‌ها</a>
                            </li>
                        </ul>
                    </li>

                    <!-- حساب‌های بانکی -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['accounts']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-university"></i>
                            <span>حساب‌های بانکی</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('account-list') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/accounts/list">لیست حساب‌ها</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('add-account') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/accounts/add">افزودن حساب جدید</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('account-transactions') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/accounts/transactions">تراکنش‌های بانکی</a>
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
                            <li class="<?php echo $this->isActiveMenu('profit-loss') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/reports/profit-loss">سود و زیان</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('cash-flow') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/reports/cash-flow">گردش نقدینگی</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('tax-report') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/reports/tax">گزارش مالیاتی</a>
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
                                <a href="<?php echo $base_url; ?>/settings/profile">تنظیمات پروفایل</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('account-settings') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/settings/account">تنظیمات حساب</a>
                            </li>
                        </ul>
                    </li>

                    <!-- خروج -->
                    <li class="nav-item logout">
                        <a href="<?php echo $base_url; ?>/auth/logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>خروج</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- اعلان‌ها -->
            <?php if (!empty($this->notifications)): ?>
            <div class="notifications">
                <div class="notifications-header">
                    <h3>اعلان‌ها</h3>
                    <span class="notification-count"><?php echo $this->getUnreadNotificationsCount(); ?></span>
                </div>
                <div class="notifications-list">
                    <?php foreach ($this->notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? 'read' : ''; ?>">
                        <div class="notification-content">
                            <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <span class="notification-time">
                                <?php echo $this->formatDate($notification['created_at']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle sidebar
                const sidebarToggle = document.querySelector('.sidebar-toggle');
                const sidebar = document.querySelector('.sidebar');
                const mainContent = document.querySelector('.main-content');
                
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed'));
                });

                // Restore sidebar state
                if (localStorage.getItem('sidebarState') === 'true') {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }

                // Submenu toggle
                const submenuLinks = document.querySelectorAll('.has-submenu');
                submenuLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const submenu = link.nextElementSibling;
                        const arrow = link.querySelector('.submenu-arrow');
                        
                        // Close other submenus
                        submenuLinks.forEach(otherLink => {
                            if (otherLink !== link) {
                                const otherSubmenu = otherLink.nextElementSibling;
                                const otherArrow = otherLink.querySelector('.submenu-arrow');
                                otherSubmenu.style.maxHeight = null;
                                otherArrow.style.transform = '';
                            }
                        });

                        // Toggle current submenu
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