<?php
class Sidebar {
    private $current_page;
    private $user_data;
    
    public function __construct() {
        $this->current_page = $this->getCurrentPage();
        $this->user_data = $this->getUserData();
    }

    private function getCurrentPage() {
        return str_replace('/pars/', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    private function getUserData() {
        if (!isset($_SESSION['user_id'])) return null;
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT u.*, r.name as role_name 
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

    private function isActiveMenu($page) {
        return strpos($this->current_page, $page) !== false;
    }

    public function render() {
        $base_url = '/pars';
        ?>
        <div class="sidebar">
            <!-- هدر سایدبار -->
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="<?php echo $base_url; ?>/assets/images/logo.svg" alt="لوگو">
                    <h2>سیستم مدیریت</h2>
                </div>
                <button class="sidebar-toggle" title="باز/بسته کردن منو">
                    <span class="toggle-icon"></span>
                </button>
            </div>

            <!-- جستجو -->
            <div class="sidebar-search">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="menuSearch" placeholder="جستجو در منو...">
                </div>
            </div>

            <!-- منوی اصلی -->
            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <li class="nav-item <?php echo $this->isActiveMenu('dashboard') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/dashboard" data-title="داشبورد">
                            <i class="fas fa-home"></i>
                            <span>داشبورد</span>
                        </a>
                    </li>
                    
                    <li class="nav-item <?php echo $this->isActiveMenu('income') ? 'active' : ''; ?>">
                        <a href="#" class="has-submenu" data-title="مدیریت درآمدها">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>مدیریت درآمدها</span>
                            <i class="fas fa-chevron-left submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="<?php echo $base_url; ?>/income/list" data-title="لیست درآمدها">
                                    <i class="fas fa-list"></i>
                                    <span>لیست درآمدها</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $base_url; ?>/income/add" data-title="ثبت درآمد جدید">
                                    <i class="fas fa-plus"></i>
                                    <span>ثبت درآمد جدید</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $base_url; ?>/income/categories" data-title="دسته‌بندی درآمدها">
                                    <i class="fas fa-tags"></i>
                                    <span>دسته‌بندی درآمدها</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?php echo $this->isActiveMenu('expense') ? 'active' : ''; ?>">
                        <a href="#" class="has-submenu" data-title="مدیریت هزینه‌ها">
                            <i class="fas fa-credit-card"></i>
                            <span>مدیریت هزینه‌ها</span>
                            <i class="fas fa-chevron-left submenu-arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="<?php echo $base_url; ?>/expense/list" data-title="لیست هزینه‌ها">
                                    <i class="fas fa-list"></i>
                                    <span>لیست هزینه‌ها</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $base_url; ?>/expense/add" data-title="ثبت هزینه جدید">
                                    <i class="fas fa-plus"></i>
                                    <span>ثبت هزینه جدید</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $base_url; ?>/expense/categories" data-title="دسته‌بندی هزینه‌ها">
                                    <i class="fas fa-tags"></i>
                                    <span>دسته‌بندی هزینه‌ها</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?php echo $this->isActiveMenu('analytics') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/analytics" data-title="آنالیز و گزارشات">
                            <i class="fas fa-chart-line"></i>
                            <span>آنالیز و گزارشات</span>
                        </a>
                    </li>

                    <li class="nav-item <?php echo $this->isActiveMenu('users') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/users" data-title="مدیریت کاربران">
                            <i class="fas fa-users"></i>
                            <span>مدیریت کاربران</span>
                        </a>
                    </li>

                    <li class="nav-item <?php echo $this->isActiveMenu('settings') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/settings" data-title="تنظیمات">
                            <i class="fas fa-cog"></i>
                            <span>تنظیمات</span>
                        </a>
                    </li>

                    <li class="nav-item <?php echo $this->isActiveMenu('profile') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/profile" data-title="پروفایل">
                            <i class="fas fa-user"></i>
                            <span>پروفایل</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php
    }
}
?>