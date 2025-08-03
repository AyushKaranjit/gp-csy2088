<?php
namespace Controllers;

/**
 * Page Controller
 * Handles serving HTML pages
 */
class PageController {
    
    /**
     * Serve static HTML files
     */
    private function servePage($filename) {
        $filePath = __DIR__ . '/../../public/' . $filename;
        
        if (file_exists($filePath)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($filePath);
        } else {
            http_response_code(404);
            echo "Page not found";
        }
    }
    
    /**
     * Home page
     */
    public function home($params = []) {
        $this->servePage('index.html');
    }
    
    /**
     * Shop/Category page
     */
    public function shop($params = []) {
        $this->servePage('category.html');
    }
    
    /**
     * Category page
     */
    public function category($params = []) {
        $this->servePage('category.html');
    }
    
    /**
     * Product detail page
     */
    public function product($params = []) {
        $this->servePage('product-detail.html');
    }
    
    /**
     * Cart page
     */
    public function cart($params = []) {
        $this->servePage('cart.html');
    }
    
    /**
     * Checkout page
     */
    public function checkout($params = []) {
        $this->servePage('payment.html');
    }
    
    /**
     * About page
     */
    public function about($params = []) {
        $this->servePage('about.html');
    }
    
    /**
     * Login page
     */
    public function login($params = []) {
        $this->servePage('login.html');
    }
    
    /**
     * Signup page
     */
    public function signup($params = []) {
        $this->servePage('signup.html');
    }
    
    /**
     * Admin page
     */
    public function admin($params = []) {
        $this->servePage('admin.html');
    }
    
    /**
     * Wishlist page
     */
    public function wishlist($params = []) {
        $this->servePage('wishlist.html');
    }
}
?>
