<?php
class DashboardManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDashboardData() {
        try {
            $data = [
                'total_income' => $this->getTotalIncome(),
                'total_expense' => $this->getTotalExpense(),
                'net_profit' => 0,
                'income_trend' => $this->calculateTrend('income'),
                'expense_trend' => $this->calculateTrend('expense'),
                'profit_trend' => 0,
                'pending_tasks' => $this->getPendingTasks(),
                'tasks_completion_rate' => $this->getTasksCompletionRate(),
                'recent_transactions' => $this->getRecentTransactions(),
                'recent_activities' => $this->getRecentActivities()
            ];

            // محاسبه سود خالص
            $data['net_profit'] = $data['total_income'] - $data['total_expense'];
            // محاسبه روند سود
            $data['profit_trend'] = $this->calculateProfitTrend();

            return $data;
        } catch (PDOException $e) {
            error_log("Error in getDashboardData: " . $e->getMessage());
            return [];
        }
    }

    private function getTotalIncome() {
        try {
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE type = 'income' 
                AND MONTH(created_at) = MONTH(CURRENT_DATE)
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Error in getTotalIncome: " . $e->getMessage());
            return 0;
        }
    }

    private function getTotalExpense() {
        try {
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE type = 'expense' 
                AND MONTH(created_at) = MONTH(CURRENT_DATE)
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Error in getTotalExpense: " . $e->getMessage());
            return 0;
        }
    }

    private function calculateTrend($type) {
        try {
            // مقدار این ماه
            $currentMonth = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE type = ? 
                AND MONTH(created_at) = MONTH(CURRENT_DATE)
            ");
            $currentMonth->execute([$type]);
            $currentTotal = $currentMonth->fetch(PDO::FETCH_ASSOC)['total'];

            // مقدار ماه قبل
            $lastMonth = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE type = ? 
                AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
            ");
            $lastMonth->execute([$type]);
            $lastTotal = $lastMonth->fetch(PDO::FETCH_ASSOC)['total'];

            // محاسبه درصد تغییر
            if ($lastTotal > 0) {
                return round((($currentTotal - $lastTotal) / $lastTotal) * 100, 2);
            }
            return 0;
        } catch (PDOException $e) {
            error_log("Error in calculateTrend: " . $e->getMessage());
            return 0;
        }
    }

    private function calculateProfitTrend() {
        try {
            // سود این ماه
            $currentMonth = $this->db->query("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as profit
                FROM transactions 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE)
            ");
            $currentProfit = $currentMonth->fetch(PDO::FETCH_ASSOC)['profit'];

            // سود ماه قبل
            $lastMonth = $this->db->query("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as profit
                FROM transactions 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
            ");
            $lastProfit = $lastMonth->fetch(PDO::FETCH_ASSOC)['profit'];

            // محاسبه درصد تغییر
            if ($lastProfit != 0) {
                return round((($currentProfit - $lastProfit) / abs($lastProfit)) * 100, 2);
            }
            return 0;
        } catch (PDOException $e) {
            error_log("Error in calculateProfitTrend: " . $e->getMessage());
            return 0;
        }
    }

    private function getPendingTasks() {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count 
                FROM tasks 
                WHERE status = 'pending'
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Error in getPendingTasks: " . $e->getMessage());
            return 0;
        }
    }

    private function getTasksCompletionRate() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM tasks
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return round(($result['completed'] / $result['total']) * 100, 2);
            }
            return 0;
        } catch (PDOException $e) {
            error_log("Error in getTasksCompletionRate: " . $e->getMessage());
            return 0;
        }
    }

    private function getRecentTransactions($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, c.name as category
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                ORDER BY t.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getRecentTransactions: " . $e->getMessage());
            return [];
        }
    }

    private function getRecentActivities($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT *
                FROM activity_logs
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getRecentActivities: " . $e->getMessage());
            return [];
        }
    }

    public function getUserData($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUserData: " . $e->getMessage());
            return null;
        }
    }

    public function formatDate($date) {
        $timestamp = strtotime($date);
        return jdate('Y/m/d H:i', $timestamp);
    }
}
?>