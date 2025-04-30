<?php

class Template {
    private static $instance = null;
    private $title = 'سیستم حسابداری';
    private $content = '';
    
    private function __construct() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
    
    public function render() {
        ob_start();
        require_once BASE_PATH . '/includes/header.php';
        
        // اضافه کردن کانتینر سایدبار
        echo '<div class="app-wrapper">';
        $sidebar = new Sidebar();
        $sidebar->render();
        
        echo '<div class="main-content">';
        echo $this->content;
        echo '</div>'; // پایان main-content
        echo '</div>'; // پایان app-wrapper
        
        require_once BASE_PATH . '/includes/footer.php';
        return ob_get_clean();
    }
}