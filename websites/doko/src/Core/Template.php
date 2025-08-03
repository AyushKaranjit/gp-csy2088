<?php
namespace Core;

/**
 * Simple Template Engine
 * Handles rendering of views with data
 */
class Template {
    private $viewsPath;
    private $data = [];
    
    public function __construct($viewsPath = null) {
        $this->viewsPath = $viewsPath ?: __DIR__ . '/../../templates';
    }
    
    /**
     * Set data for template
     */
    public function setData($key, $value = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }
    
    /**
     * Render a template
     */
    public function render($template, $data = []) {
        $this->setData($data);
        
        $templatePath = $this->viewsPath . '/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }
        
        // Extract data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include template
        include $templatePath;
        
        // Get contents and clean buffer
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Render and output template
     */
    public function display($template, $data = []) {
        echo $this->render($template, $data);
    }
    
    /**
     * Include a partial template
     */
    public function partial($template, $data = []) {
        $partialData = array_merge($this->data, $data);
        extract($partialData);
        
        $templatePath = $this->viewsPath . '/' . $template . '.php';
        
        if (file_exists($templatePath)) {
            include $templatePath;
        }
    }
    
    /**
     * Escape HTML entities
     */
    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format currency
     */
    public function currency($amount) {
        return 'रू ' . number_format($amount, 2);
    }
    
    /**
     * Format date
     */
    public function date($date, $format = 'Y-m-d H:i:s') {
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Generate URL
     */
    public function url($path = '') {
        $baseUrl = '/websites/doko/public';
        return $baseUrl . '/' . ltrim($path, '/');
    }
    
    /**
     * Generate asset URL
     */
    public function asset($path) {
        return $this->url($path);
    }
}
?>
