<?php
session_start();
require_once '../config/database.php';

class UserAuthentication {
    private $db;
    private $error_message = '';
    private $login_attempts = 0;
    private $last_attempt_time = null;
    private $lockout_duration = 900; // 15 minutes in seconds
    private $max_attempts = 5;
    
    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->initializeSession();
    }
    
    private function initializeSession() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        if (!isset($_SESSION['last_attempt_time'])) {
            $_SESSION['last_attempt_time'] = null;
        }
        
        $this->login_attempts = $_SESSION['login_attempts'];
        $this->last_attempt_time = $_SESSION['last_attempt_time'];
    }
    
    public function isAccountLocked() {
        if ($this->login_attempts >= $this->max_attempts) {
            if ($this->last_attempt_time !== null) {
                $time_elapsed = time() - strtotime($this->last_attempt_time);
                if ($time_elapsed < $this->lockout_duration) {
                    return true;
                } else {
                    // Reset attempts after lockout period
                    $this->resetAttempts();
                }
            }
        }
        return false;
    }
    
    public function getLockoutTimeRemaining() {
        if ($this->last_attempt_time !== null) {
            $time_elapsed = time() - strtotime($this->last_attempt_time);
            $time_remaining = $this->lockout_duration - $time_elapsed;
            return max(0, $time_remaining);
        }
        return 0;
    }
    
    private function incrementAttempts() {
        $this->login_attempts++;
        $_SESSION['login_attempts'] = $this->login_attempts;
        $_SESSION['last_attempt_time'] = date('Y-m-d H:i:s');
        $this->last_attempt_time = $_SESSION['last_attempt_time'];
    }
    
    private function resetAttempts() {
        $this->login_attempts = 0;
        $this->last_attempt_time = null;
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = null;
    }
    
    public function validateLogin($email, $password) {
        // Check if account is locked
        if ($this->isAccountLocked()) {
            $remaining_time = ceil($this->getLockoutTimeRemaining() / 60);
            return [
                'success' => false,
                'message' => "حساب کاربری شما به دلیل تلاش‌های ناموفق قفل شده است. لطفاً {$remaining_time} دقیقه دیگر امتحان کنید."
            ];
        }
        
        try {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->incrementAttempts();
                return [
                    'success' => false,
                    'message' => 'فرمت ایمیل نامعتبر است'
                ];
            }
            
            // Query user data
            $query = "SELECT id, email, password, fullname, status, last_login 
                     FROM users 
                     WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Check account status
                    if ($user['status'] !== 'active') {
                        return [
                            'success' => false,
                            'message' => 'حساب کاربری شما غیرفعال است. لطفاً با پشتیبانی تماس بگیرید.'
                        ];
                    }
                    
                    // Reset attempts on successful login
                    $this->resetAttempts();
                    
                    // Update last login time
                    $this->updateLastLogin($user['id']);
                    
                    // Set session data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['fullname'];
                    $_SESSION['last_activity'] = time();
                    
                    // Log login activity
                    $this->logLoginActivity($user['id'], true);
                    
                    return [
                        'success' => true,
                        'user' => $user
                    ];
                }
            }
            
            // Increment failed attempts
            $this->incrementAttempts();
            
            // Log failed login attempt
            $this->logLoginActivity(0, false, $email);
            
            return [
                'success' => false,
                'message' => 'ایمیل یا رمز عبور اشتباه است'
            ];
            
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطا در ارتباط با پایگاه داده: ' . $e->getMessage()
            ];
        }
    }
    
    private function updateLastLogin($user_id) {
        $query = "UPDATE users 
                 SET last_login = NOW() 
                 WHERE id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }
    
    private function logLoginActivity($user_id, $success, $email = null) {
        $query = "INSERT INTO login_logs 
                 (user_id, ip_address, user_agent, status, email, created_at) 
                 VALUES 
                 (:user_id, :ip_address, :user_agent, :status, :email, NOW())";
        
        $stmt = $this->db->prepare($query);
        
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $status = $success ? 'success' : 'failed';
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":user_agent", $user_agent);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":email", $email);
        
        $stmt->execute();
    }
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $auth = new UserAuthentication($database);
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $result = $auth->validateLogin($email, $password);
    
    if ($result['success']) {
        // Redirect to dashboard
        header("Location: ../dashboard/");
        exit();
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم حسابداری</title>
    <link rel="stylesheet" href="../assets/css/fonts.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="gradient-bg">
    <div class="auth-container">
        <div class="auth-box login-box">
            <div class="auth-header" data-parallax="0.2">
                <div class="logo-container">
                    <img src="../assets/images/logo.svg" alt="لوگو" class="logo">
                </div>
                <h2>ورود به سیستم</h2>
                <p class="auth-subtitle">خوش آمدید! لطفاً اطلاعات حساب خود را وارد کنید</p>
            </div>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <div class="alert-icon">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                    </div>
                    <div class="alert-content">
                        <?php echo $error_message; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="auth-form login-form">
                <div class="form-section" data-parallax="0.1">
                    <div class="form-group floating">
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               class="form-control">
                        <label for="email">ایمیل</label>
                        <div class="form-effect"></div>
                    </div>

                    <div class="form-group floating">
                        <input type="password" id="password" name="password" required class="form-control">
                        <label for="password">رمز عبور</label>
                        <div class="form-effect"></div>
                        <button type="button" class="password-toggle" aria-label="نمایش/پنهان کردن رمز عبور">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            مرا به خاطر بسپار
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                        </label>
                        <a href="forgot-password.php" class="forgot-password">فراموشی رمز عبور؟</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary login-btn" data-parallax="0.15">
                    <span class="btn-text">ورود به سیستم</span>
                    <div class="btn-effect"></div>
                    <div class="btn-loader"></div>
                </button>

                <div class="divider">
                    <span>یا</span>
                </div>

                <div class="social-login" data-parallax="0.2">
                    <button type="button" class="btn btn-google">
                        <img src="../assets/images/google-icon.svg" alt="Google">
                        <span>ورود با گوگل</span>
                    </button>
                </div>
            </form>

            <div class="auth-links" data-parallax="0.25">
                <p>حساب کاربری ندارید؟ <a href="register.php" class="link-effect">ثبت نام کنید</a></p>
            </div>
        </div>
    </div>

    <div class="auth-background">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <script src="../assets/js/auth.js"></script>
    <script>
        // Additional login page specific JavaScript
        document.addEventListener('DOMContentLoaded', () => {
            // Password visibility toggle
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    const input = e.currentTarget.previousElementSibling;
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    
                    // Update icon
                    const icon = toggle.querySelector('.icon');
                    if (type === 'text') {
                        icon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
                    } else {
                        icon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
                    }
                });
            });

            // Form validation
            const form = document.querySelector('.login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const submitButton = document.querySelector('.login-btn');

            form.addEventListener('submit', (e) => {
                let isValid = true;
                const errorMessages = [];

                // Email validation
                if (!emailInput.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    errorMessages.push('لطفاً یک ایمیل معتبر وارد کنید');
                    isValid = false;
                }

                // Password validation
                if (passwordInput.value.length < 8) {
                    errorMessages.push('رمز عبور باید حداقل 8 کاراکتر باشد');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    // Show error messages
                    const errorContainer = document.createElement('div');
                    errorContainer.className = 'alert alert-danger';
                    errorContainer.innerHTML = errorMessages.join('<br>');
                    form.insertBefore(errorContainer, form.firstChild);

                    // Remove error message after 3 seconds
                    setTimeout(() => {
                        errorContainer.remove();
                    }, 3000);
                } else {
                    submitButton.classList.add('loading');
                }
            });

            // Remember me functionality
            const rememberCheckbox = document.querySelector('input[name="remember"]');
            if (localStorage.getItem('remembered_email')) {
                emailInput.value = localStorage.getItem('remembered_email');
                rememberCheckbox.checked = true;
            }

            rememberCheckbox.addEventListener('change', () => {
                if (rememberCheckbox.checked) {
                    localStorage.setItem('remembered_email', emailInput.value);
                } else {
                    localStorage.removeItem('remembered_email');
                }
            });
        });
    </script>
</body>
</html>