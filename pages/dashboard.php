<?php
require_once '../includes/init.php';
require_once '../includes/auth_check.php';
require_once '../classes/DashboardManager.php';

$dashboard = new DashboardManager();
$data = $dashboard->getDashboardData();

// دریافت اطلاعات کاربر
$user_data = $dashboard->getUserData($_SESSION['user_id']);

// تنظیم عنوان صفحه
$page_title = 'داشبورد مدیریت';
require_once '../includes/header.php';
?>

<div class="dashboard-container">
    <!-- نوار بالایی -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>خوش آمدید، <?php echo htmlspecialchars($user_data['fullname']); ?></h1>
            <p class="current-date" id="currentDate"></p>
        </div>
        <div class="quick-actions">
            <button class="btn btn-primary" onclick="showQuickAdd('income')">
                <i class="fas fa-plus"></i>
                ثبت درآمد جدید
            </button>
            <button class="btn btn-danger" onclick="showQuickAdd('expense')">
                <i class="fas fa-minus"></i>
                ثبت هزینه جدید
            </button>
            <button class="btn btn-info" onclick="showReportGenerator()">
                <i class="fas fa-file-export"></i>
                گزارش‌گیری سریع
            </button>
        </div>
    </div>

    <!-- کارت‌های آماری -->
    <div class="stats-cards">
        <div class="stat-card income">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <h3>درآمد کل</h3>
                <p class="amount"><?php echo number_format($data['total_income']); ?> تومان</p>
                <div class="trend <?php echo $data['income_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-<?php echo $data['income_trend'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <span><?php echo abs($data['income_trend']); ?>%</span>
                    <span class="trend-period">نسبت به ماه قبل</span>
                </div>
            </div>
        </div>

        <div class="stat-card expense">
            <div class="stat-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="stat-details">
                <h3>هزینه کل</h3>
                <p class="amount"><?php echo number_format($data['total_expense']); ?> تومان</p>
                <div class="trend <?php echo $data['expense_trend'] <= 0 ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-<?php echo $data['expense_trend'] <= 0 ? 'arrow-down' : 'arrow-up'; ?>"></i>
                    <span><?php echo abs($data['expense_trend']); ?>%</span>
                    <span class="trend-period">نسبت به ماه قبل</span>
                </div>
            </div>
        </div>

        <div class="stat-card profit">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <h3>سود خالص</h3>
                <p class="amount"><?php echo number_format($data['net_profit']); ?> تومان</p>
                <div class="trend <?php echo $data['profit_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-<?php echo $data['profit_trend'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <span><?php echo abs($data['profit_trend']); ?>%</span>
                    <span class="trend-period">نسبت به ماه قبل</span>
                </div>
            </div>
        </div>

        <div class="stat-card tasks">
            <div class="stat-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-details">
                <h3>وظایف معوق</h3>
                <p class="amount"><?php echo $data['pending_tasks']; ?></p>
                <div class="task-progress">
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $data['tasks_completion_rate']; ?>%"></div>
                    </div>
                    <span><?php echo $data['tasks_completion_rate']; ?>% تکمیل شده</span>
                </div>
            </div>
        </div>
    </div>

    <!-- نمودارهای اصلی -->
    <div class="main-charts">
        <div class="chart-container financial-overview">
            <div class="chart-header">
                <h2>نمای کلی مالی</h2>
                <div class="chart-controls">
                    <select id="financialChartPeriod" onchange="updateFinancialChart()">
                        <option value="week">هفته</option>
                        <option value="month" selected>ماه</option>
                        <option value="quarter">سه ماه</option>
                        <option value="year">سال</option>
                    </select>
                    <div class="chart-actions">
                        <button onclick="downloadChartAsPNG('financialChart')" title="دانلود تصویر">
                            <i class="fas fa-download"></i>
                        </button>
                        <button onclick="toggleChartFullscreen('financialChart')" title="نمایش تمام صفحه">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="financialChart"></canvas>
            </div>
        </div>

        <div class="chart-container category-analysis">
            <div class="chart-header">
                <h2>تحلیل دسته‌بندی‌ها</h2>
                <div class="chart-controls">
                    <select id="categoryChartType" onchange="updateCategoryChart()">
                        <option value="income">درآمدها</option>
                        <option value="expense">هزینه‌ها</option>
                    </select>
                    <div class="chart-actions">
                        <button onclick="downloadChartAsPNG('categoryChart')" title="دانلود تصویر">
                            <i class="fas fa-download"></i>
                        </button>
                        <button onclick="toggleChartFullscreen('categoryChart')" title="نمایش تمام صفحه">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- آخرین تراکنش‌ها و فعالیت‌ها -->
    <div class="dashboard-bottom">
        <div class="recent-transactions">
            <div class="section-header">
                <h2>آخرین تراکنش‌ها</h2>
                <a href="transactions.php" class="view-all">مشاهده همه</a>
            </div>
            <div class="transactions-list">
                <?php foreach ($data['recent_transactions'] as $transaction): ?>
                <div class="transaction-item <?php echo $transaction['type']; ?>">
                    <div class="transaction-icon">
                        <i class="fas fa-<?php echo $transaction['type'] === 'income' ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    </div>
                    <div class="transaction-details">
                        <h4><?php echo htmlspecialchars($transaction['title']); ?></h4>
                        <p class="category"><?php echo htmlspecialchars($transaction['category']); ?></p>
                    </div>
                    <div class="transaction-amount">
                        <span class="amount"><?php echo number_format($transaction['amount']); ?> تومان</span>
                        <span class="date"><?php echo $dashboard->formatDate($transaction['created_at']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="recent-activities">
            <div class="section-header">
                <h2>فعالیت‌های اخیر</h2>
                <a href="activities.php" class="view-all">مشاهده همه</a>
            </div>
            <div class="activities-list">
                <?php foreach ($data['recent_activities'] as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-<?php echo $activity['icon']; ?>"></i>
                    </div>
                    <div class="activity-details">
                        <p><?php echo htmlspecialchars($activity['description']); ?></p>
                        <span class="time"><?php echo $dashboard->formatDate($activity['created_at']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- مودال ثبت سریع -->
<div class="modal" id="quickAddModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">ثبت سریع</h3>
            <button class="close-modal" onclick="closeModal('quickAddModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="quickAddForm" onsubmit="submitQuickAdd(event)">
                <input type="hidden" id="transactionType" name="type" value="income">
                
                <div class="form-group">
                    <label for="title">عنوان</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="amount">مبلغ (تومان)</label>
                    <input type="number" id="amount" name="amount" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="category">دسته‌بندی</label>
                    <select id="category" name="category" required>
                        <!-- گزینه‌ها با JavaScript پر می‌شود -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">توضیحات</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        ثبت
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('quickAddModal')">
                        <i class="fas fa-times"></i>
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال گزارش‌گیری سریع -->
<div class="modal" id="reportModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>گزارش‌گیری سریع</h3>
            <button class="close-modal" onclick="closeModal('reportModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="reportForm" onsubmit="generateReport(event)">
                <div class="form-group">
                    <label for="reportType">نوع گزارش</label>
                    <select id="reportType" name="reportType" required>
                        <option value="financial">گزارش مالی</option>
                        <option value="category">گزارش دسته‌بندی</option>
                        <option value="comparison">گزارش مقایسه‌ای</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dateRange">بازه زمانی</label>
                    <select id="dateRange" name="dateRange" required>
                        <option value="today">امروز</option>
                        <option value="week">هفته جاری</option>
                        <option value="month">ماه جاری</option>
                        <option value="quarter">سه ماه اخیر</option>
                        <option value="year">سال جاری</option>
                        <option value="custom">بازه دلخواه</option>
                    </select>
                </div>
                
                <div id="customDateRange" style="display: none;">
                    <div class="form-group">
                        <label for="startDate">از تاریخ</label>
                        <input type="date" id="startDate" name="startDate">
                    </div>
                    <div class="form-group">
                        <label for="endDate">تا تاریخ</label>
                        <input type="date" id="endDate" name="endDate">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="format">قالب خروجی</label>
                    <select id="format" name="format" required>
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="print">پرینت مستقیم</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-export"></i>
                        تولید گزارش
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('reportModal')">
                        <i class="fas fa-times"></i>
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>