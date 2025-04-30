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
        require_once BASE_PATH . '/includes/header.php';
        $sidebar = new Sidebar();
        $sidebar->render();
        echo '<div class="main-content">';
        echo $this->content;
        echo '</div>';
        require_once BASE_PATH . '/includes/footer.php';
    }
}