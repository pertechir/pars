<?php
class Sidebar {
    private $current_page;
    private $user_data;
    private $notifications;
    private $system_status;
    private $recent_activities;
    private $favorite_menus;
    private $theme;
    private $language;
    private $online_users;
    
    public function __construct() {
        $this->current_page = $this->getCurrentPage();
        $this->user_data = $this->getUserData();
        $this->notifications = $this->getNotifications();
        $this->system_status = $this->getSystemStatus();
        $this->recent_activities = $this->getRecentActivities();
        $this->favorite_menus = $this->getFavoriteMenus();
        $this->theme = $this->getUserTheme();
        $this->language = $this->getUserLanguage();
        $this->online_users = $this->getOnlineUsers();
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
                        u.phone,
                        u.status,
                        u.last_login,
                        r.name as role_name,
                        r.permissions
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE u.id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // اضافه کردن تنظیمات کاربر
                $stmt = $db->prepare("SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                
                return array_merge($user, ['settings' => $settings]);
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
                LIMIT 10
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching notifications: " . $e->getMessage());
            return [];
        }
    }
    
    private function getSystemStatus() {
        try {
            $db = Database::getInstance();
            
            // بررسی وضعیت سیستم
            $status = [
                'disk_usage' => disk_free_space('/') / disk_total_space('/') * 100,
                'memory_usage' => memory_get_usage(true) / 1024 / 1024,
                'cpu_load' => sys_getloadavg()[0],
                'db_size' => $this->getDatabaseSize($db),
                'active_sessions' => $this->getActiveSessions($db),
                'last_backup' => $this->getLastBackupTime($db)
            ];
            
            return $status;
        } catch (Exception $e) {
            error_log("Error getting system status: " . $e->getMessage());
            return [];
        }
    }
    
    private function getDatabaseSize($db) {
        try {
            $stmt = $db->query("
                SELECT 
                    SUM(data_length + index_length) / 1024 / 1024 AS size_mb 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    private function getActiveSessions($db) {
        try {
            $stmt = $db->query("
                SELECT COUNT(*) 
                FROM sessions 
                WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    private function getLastBackupTime($db) {
        try {
            $stmt = $db->query("
                SELECT created_at 
                FROM system_backups 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    private function getRecentActivities() {
        if (!isset($_SESSION['user_id'])) return [];
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT 
                    a.id,
                    a.action_type,
                    a.entity_type,
                    a.entity_id,
                    a.description,
                    a.created_at,
                    u.fullname as user_name
                FROM activity_logs a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent activities: " . $e->getMessage());
            return [];
        }
    }
    
    private function getFavoriteMenus() {
        if (!isset($_SESSION['user_id'])) return [];
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT 
                    fm.id,
                    fm.menu_name,
                    fm.menu_url,
                    fm.icon
                FROM favorite_menus fm
                WHERE fm.user_id = ?
                ORDER BY fm.sort_order
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching favorite menus: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUserTheme() {
        return isset($_SESSION['user_theme']) ? $_SESSION['user_theme'] : 'light';
    }
    
    private function getUserLanguage() {
        return isset($_SESSION['user_language']) ? $_SESSION['user_language'] : 'fa';
    }
    
    private function getOnlineUsers() {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT 
                    u.id,
                    u.fullname,
                    u.email,
                    s.last_activity
                FROM users u
                INNER JOIN sessions s ON u.id = s.user_id
                WHERE s.last_activity >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching online users: " . $e->getMessage());
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
    
    private function renderUserProfile() {
        if (!$this->user_data) return;
        ?>
        <div class="sidebar-user">
            <div class="user-profile">
                <div class="profile-image">
                    <img src="<?php echo $this->getUserAvatar(); ?>" alt="تصویر پروفایل">
                    <span class="status-indicator <?php echo $this->user_data['status']; ?>"></span>
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($this->user_data['fullname']); ?></h3>
                    <p><?php echo htmlspecialchars($this->user_data['role_name']); ?></p>
                    <span class="last-login">
                        آخرین ورود: <?php echo $this->formatDate($this->user_data['last_login']); ?>
                    </span>
                </div>
            </div>
            <div class="user-actions">
                <button class="btn-profile" onclick="location.href='/pars/settings/profile'">
                    <i class="fas fa-user-cog"></i>
                </button>
                <button class="btn-notifications" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <?php if ($this->getUnreadNotificationsCount() > 0): ?>
                        <span class="notification-badge"><?php echo $this->getUnreadNotificationsCount(); ?></span>
                    <?php endif; ?>
                </button>
                <button class="btn-theme" onclick="toggleTheme()">
                    <i class="fas fa-<?php echo $this->theme === 'dark' ? 'sun' : 'moon'; ?>"></i>
                </button>
            </div>
        </div>
        <?php
    }
    
    private function getUserAvatar() {
        $avatar_path = '/pars/assets/images/avatars/' . $_SESSION['user_id'] . '.jpg';
        return file_exists($_SERVER['DOCUMENT_ROOT'] . $avatar_path) 
            ? $avatar_path 
            : '/pars/assets/images/default-avatar.png';
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
        return jdate('Y/m/d H:i', strtotime($date));
    }
    
    private function getUnreadNotificationsCount() {
        return count(array_filter($this->notifications, function($n) {
            return !$n['is_read'];
        }));
    }
    
    private function renderSearchBox() {
        ?>
        <div class="sidebar-search">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="menuSearch" placeholder="جستجو در منو..." onkeyup="searchMenu(this.value)">
                <i class="fas fa-times" onclick="clearSearch()"></i>
            </div>
        </div>
        <?php
    }
    
    public function render() {
        $base_url = '/pars';
        ?>
        <div class="sidebar <?php echo $this->theme; ?>-theme">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="<?php echo $base_url; ?>/assets/images/logo.svg" alt="لوگو" class="logo">
                    <h2>سیستم حسابداری</h2>
                </div>
                <button class="sidebar-toggle" title="باز/بسته کردن منو">
                    <span class="toggle-icon"></span>
                </button>
            </div>

            <?php $this->renderUserProfile(); ?>
            <?php $this->renderSearchBox(); ?>

            <div class="system-status">
                <div class="status-item">
                    <i class="fas fa-server"></i>
                    <span>وضعیت سیستم:</span>
                    <span class="status <?php echo $this->getSystemHealthStatus(); ?>">
                        <?php echo $this->getSystemHealthText(); ?>
                    </span>
                </div>
                <div class="status-details">
                    <div class="detail-item">
                        <label>دیسک:</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $this->system_status['disk_usage']; ?>%"></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <label>حافظه:</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo min($this->system_status['memory_usage'] / 1024 * 100, 100); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <!-- داشبورد -->
                    <li class="nav-item <?php echo $this->isActiveMenu('dashboard') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/dashboard" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>داشبورد</span>
                        </a>
                    </li>

                    <!-- منوهای پرکاربرد -->
                    <?php if (!empty($this->favorite_menus)): ?>
                    <li class="nav-section">
                        <span class="section-title">دسترسی سریع</span>
                        <ul class="favorite-menus">
                            <?php foreach ($this->favorite_menus as $menu): ?>
                            <li class="<?php echo $this->isActiveMenu($menu['menu_url']) ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url . '/' . $menu['menu_url']; ?>">
                                    <i class="<?php echo $menu['icon']; ?>"></i>
                                    <span><?php echo htmlspecialchars($menu['menu_name']); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <!-- مدیریت مالی -->
                    <li class="nav-section">
                        <span class="section-title">مدیریت مالی</span>
                        
                        <!-- درآمدها -->
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
                                <li class="<?php echo $this->isActiveMenu('income-report') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/income/report">گزارش درآمدها</a>
                                </li>
                            </ul>
                        </li>

                        <!-- هزینه‌ها -->
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
                                <li class="<?php echo $this->isActiveMenu('expense-report') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/expense/report">گزارش هزینه‌ها</a>
                                </li>
                            </ul>
                        </li>
                    </li>

                    <!-- مدیریت حساب‌های بانکی -->
                    <li class="nav-section">
                        <span class="section-title">امور بانکی</span>
                        
                        <li class="nav-item <?php echo $this->isActiveSubmenu(['accounts', 'add-account', 'account-transactions']) ? 'active' : ''; ?>">
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
                                <li class="<?php echo $this->isActiveMenu('account-transfer') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/accounts/transfer">انتقال بین حساب‌ها</a>
                                </li>
                            </ul>
                        </li>
                    </li>

                    <!-- مدیریت چک‌ها -->
                    <li class="nav-item <?php echo $this->isActiveSubmenu(['checks']) ? 'active' : ''; ?>">
                        <a href="#" class="nav-link has-submenu">
                            <i class="fas fa-money-check-alt"></i>
                            <span>مدیریت چک‌ها</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li class="<?php echo $this->isActiveMenu('received-checks') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/checks/received">چک‌های دریافتی</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('issued-checks') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/checks/issued">چک‌های پرداختی</a>
                            </li>
                            <li class="<?php echo $this->isActiveMenu('check-report') ? 'active' : ''; ?>">
                                <a href="<?php echo $base_url; ?>/checks/report">گزارش چک‌ها</a>
                            </li>
                        </ul>
                    </li>

                    <!-- گزارشات -->
                    <li class="nav-section">
                        <span class="section-title">گزارشات و آمار</span>
                        
                        <li class="nav-item <?php echo $this->isActiveSubmenu(['reports']) ? 'active' : ''; ?>">
                            <a href="#" class="nav-link has-submenu">
                                <i class="fas fa-chart-line"></i>
                                <span>گزارشات</span>
                                <i class="fas fa-chevron-down submenu-arrow"></i>
                            </a>
                            <ul class="submenu">
                                <li class="<?php echo $this->isActiveMenu('balance-sheet') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/reports/balance-sheet">تراز مالی</a>
                                </li>
                                <li class="<?php echo $this->isActiveMenu('profit-loss') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/reports/profit-loss">سود و زیان</a>
                                </li>
                                <li class="<?php echo $this->isActiveMenu('cash-flow') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/reports/cash-flow">گردش نقدینگی</a>
                                </li>
                                <li class="<?php echo $this->isActiveMenu('tax-report') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/reports/tax">گزارش مالیاتی</a>
                                </li>
                                <li class="<?php echo $this->isActiveMenu('custom-report') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/reports/custom">گزارش‌ساز</a>
                                </li>
                            </ul>
                        </li>
                    </li>

                    <!-- تنظیمات -->
                    <li class="nav-section">
                        <span class="section-title">تنظیمات</span>
                        
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
                                <li class="<?php echo $this->isActiveMenu('notification-settings') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/settings/notifications">تنظیمات اعلان‌ها</a>
                                </li>
                                <li class="<?php echo $this->isActiveMenu('backup-settings') ? 'active' : ''; ?>">
                                    <a href="<?php echo $base_url; ?>/settings/backup">پشتیبان‌گیری</a>
                                </li>
                            </ul>
                        </li>
                    </li>

                    <!-- کاربران آنلاین -->
                    <?php if (!empty($this->online_users)): ?>
                    <li class="nav-section">
                        <span class="section-title">کاربران آنلاین</span>
                        <div class="online-users">
                            <?php foreach ($this->online_users as $user): ?>
                                <div class="online-user" title="<?php echo htmlspecialchars($user['fullname']); ?>">
                                    <img src="<?php echo $this->getUserAvatarByEmail($user['email']); ?>" 
                                         alt="<?php echo htmlspecialchars($user['fullname']); ?>">
                                    <span class="online-indicator"></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <?php endif; ?>

                    <!-- خروج -->
                    <li class="nav-item logout">
                        <a href="<?php echo $base_url; ?>/auth/logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>خروج</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- نمایش اعلان‌ها -->
            <div class="notifications-panel" id="notificationsPanel">
                <div class="notifications-header">
                    <h3>اعلان‌ها</h3>
                    <button onclick="markAllNotificationsAsRead()">
                        <i class="fas fa-check-double"></i>
                        خواندن همه
                    </button>
                </div>
                <div class="notifications-list">
                    <?php if (empty($this->notifications)): ?>
                        <div class="no-notifications">
                            <i class="fas fa-bell-slash"></i>
                            <p>اعلان جدیدی ندارید</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($this->notifications as $notification): ?>
                            <div class="notification-item <?php echo $notification['is_read'] ? 'read' : ''; ?>"
                                 data-id="<?php echo $notification['id']; ?>">
                                <div class="notification-icon">
                                    <i class="fas fa-<?php echo $this->getNotificationIcon($notification['type']); ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <span class="notification-time">
                                        <?php echo $this->formatDate($notification['created_at']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
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

                // Search functionality
                window.searchMenu = function(value) {
                    const menuItems = document.querySelectorAll('.nav-item');
                    const searchValue = value.toLowerCase();

                    menuItems.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        const shouldShow = text.includes(searchValue);
                        item.style.display = shouldShow ? '' : 'none';

                        // اگر زیرمنو دارد و متن در زیرمنو پیدا شد، منوی اصلی را هم نمایش دهد
                        if (!shouldShow && item.querySelector('.submenu')) {
                            const submenuText = item.querySelector('.submenu').textContent.toLowerCase();
                            if (submenuText.includes(searchValue)) {
                                item.style.display = '';
                                // باز کردن زیرمنو
                                const submenu = item.querySelector('.submenu');
                                const arrow = item.querySelector('.submenu-arrow');
                                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                                arrow.style.transform = 'rotate(180deg)';
                            }
                        }
                    });
                };

                // Clear search
                window.clearSearch = function() {
                    document.getElementById('menuSearch').value = '';
                    const menuItems = document.querySelectorAll('.nav-item');
                    menuItems.forEach(item => {
                        item.style.display = '';
                    });
                };

                // Toggle notifications panel
                window.toggleNotifications = function() {
                    const panel = document.getElementById('notificationsPanel');
                    panel.classList.toggle('show');
                };

                // Mark notification as read
                window.markNotificationAsRead = function(notificationId) {
                    fetch('/pars/api/notifications/mark-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: notificationId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`.notification-item[data-id="${notificationId}"]`)
                                .classList.add('read');
                            updateNotificationBadge();
                        }
                    });
                };

                // Mark all notifications as read
                window.markAllNotificationsAsRead = function() {
                    fetch('/pars/api/notifications/mark-all-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('.notification-item')
                                .forEach(item => item.classList.add('read'));
                            updateNotificationBadge();
                        }
                    });
                };

                // Update notification badge
                function updateNotificationBadge() {
                    const unreadCount = document.querySelectorAll('.notification-item:not(.read)').length;
                    const badge = document.querySelector('.notification-badge');
                    if (unreadCount > 0) {
                        if (!badge) {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = unreadCount;
                            document.querySelector('.btn-notifications').appendChild(newBadge);
                        } else {
                            badge.textContent = unreadCount;
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }

                // Toggle theme
                window.toggleTheme = function() {
                    const sidebar = document.querySelector('.sidebar');
                    const currentTheme = sidebar.classList.contains('dark-theme') ? 'light' : 'dark';
                    
                    // Update theme class
                    sidebar.classList.remove('light-theme', 'dark-theme');
                    sidebar.classList.add(`${currentTheme}-theme`);

                    // Update theme icon
                    const themeIcon = document.querySelector('.btn-theme i');
                    themeIcon.classList.remove('fa-sun', 'fa-moon');
                    themeIcon.classList.add(currentTheme === 'dark' ? 'fa-sun' : 'fa-moon');

                    // Save theme preference
                    fetch('/pars/api/settings/update-theme', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ theme: currentTheme })
                    });
                };

                // Click outside to close
                document.addEventListener('click', function(event) {
                    const notifications = document.getElementById('notificationsPanel');
                    const notificationBtn = document.querySelector('.btn-notifications');
                    
                    if (!notifications.contains(event.target) && 
                        !notificationBtn.contains(event.target)) {
                        notifications.classList.remove('show');
                    }
                });

                // Handle favorite menus
                const favoriteToggles = document.querySelectorAll('.favorite-toggle');
                favoriteToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        const menuId = this.dataset.menuId;
                        const isFavorite = this.classList.contains('active');

                        fetch('/pars/api/menus/toggle-favorite', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ 
                                menuId: menuId,
                                isFavorite: !isFavorite 
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.classList.toggle('active');
                            }
                        });
                    });
                });
            });
        </script>
        <?php
    }

    private function getSystemHealthStatus() {
        $disk_threshold = 90; // درصد
        $memory_threshold = 80; // درصد
        $cpu_threshold = 80; // درصد

        if ($this->system_status['disk_usage'] > $disk_threshold ||
            ($this->system_status['memory_usage'] / 1024 * 100) > $memory_threshold ||
            ($this->system_status['cpu_load'] * 100) > $cpu_threshold) {
            return 'warning';
        }
        return 'normal';
    }

    private function getSystemHealthText() {
        $status = $this->getSystemHealthStatus();
        return $status === 'normal' ? 'عادی' : 'هشدار';
    }

    private function getNotificationIcon($type) {
        $icons = [
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'times-circle',
            'info' => 'info-circle',
            'money' => 'money-bill-wave',
            'user' => 'user',
            'system' => 'cog'
        ];
        return $icons[$type] ?? 'bell';
    }

    private function getUserAvatarByEmail($email) {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=32';
    }
}
?>
                        