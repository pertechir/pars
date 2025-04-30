<?php
session_start();
require_once '../config/database.php';

class UserRegistration {
    private $db;
    private $error_message = '';
    private $success_message = '';
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    public function validateInput($data) {
        $errors = [];
        
        if (empty($data['fullname']) || strlen($data['fullname']) < 3) {
            $errors[] = 'نام و نام خانوادگی باید حداقل 3 کاراکتر باشد';
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'لطفا یک ایمیل معتبر وارد کنید';
        }
        
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'رمز عبور باید حداقل 8 کاراکتر باشد';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند';
        }
        
        if (!preg_match('/^[0-9]{11}$/', $data['phone'])) {
            $errors[] = 'شماره تلفن باید 11 رقم باشد';
        }
        
        return $errors;
    }
    
    public function registerUser($data) {
        try {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (
                fullname, email, password, phone, 
                created_at, status
            ) VALUES (
                :fullname, :email, :password, :phone,
                NOW(), 'active'
            )";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(":fullname", $data['fullname']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":phone", $data['phone']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            $this->error_message = "خطا در ثبت اطلاعات: " . $e->getMessage();
            return false;
        }
    }
}

// پردازش ثبت نام
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $registration = new UserRegistration($database);
    
    $validation_errors = $registration->validateInput($_POST);
    
    if (empty($validation_errors)) {
        if ($registration->registerUser($_POST)) {
            header("Location: login.php?registered=1");
            exit();
        }
    } else {
        $error_message = implode('<br>', $validation_errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت نام | سیستم حسابداری</title>
    <link rel="stylesheet" href="../assets/css/fonts.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="gradient-bg">
    <div class="auth-container">
        <div class="auth-box registration-box">
            <div class="auth-header">
                <h2>ثبت نام در سیستم حسابداری</h2>
                <p class="auth-subtitle">لطفا اطلاعات خود را وارد کنید</p>
            </div>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="auth-form">
                <div class="form-section">
                    <div class="form-group floating">
                        <input type="text" id="fullname" name="fullname" required 
                               value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                        <label for="fullname">نام و نام خانوادگی</label>
                        <div class="form-effect"></div>
                    </div>

                    <div class="form-group floating">
                        <input type="email" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <label for="email">ایمیل</label>
                        <div class="form-effect"></div>
                    </div>

                    <div class="form-group floating">
                        <input type="tel" id="phone" name="phone" required pattern="[0-9]{11}"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        <label for="phone">شماره موبایل</label>
                        <div class="form-effect"></div>
                    </div>

                    <div class="form-group floating">
                        <input type="password" id="password" name="password" required minlength="8">
                        <label for="password">رمز عبور</label>
                        <div class="form-effect"></div>
                    </div>

                    <div class="form-group floating">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        <label for="confirm_password">تکرار رمز عبور</label>
                        <div class="form-effect"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span class="btn-text">ثبت نام</span>
                    <div class="btn-effect"></div>
                </button>
            </form>

            <div class="auth-links">
                <a href="login.php" class="link-effect">قبلاً ثبت نام کرده‌اید؟ وارد شوید</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>