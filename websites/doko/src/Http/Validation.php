<?php

namespace Doko\Http;

class Validation
{
    private $errors = [];
    private $data = [];
    
    public function __construct($data = [])
    {
        $this->data = $data;
    }
    
    /**
     * Validate required fields
     */
    public function required($field, $message = null)
    {
        $value = $this->getValue($field);
        if (empty($value) && $value !== '0') {
            $this->addError($field, $message ?? ucfirst($field) . ' is required');
        }
        return $this;
    }
    
    /**
     * Validate email format
     */
    public function email($field, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message ?? 'Invalid email format');
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength($field, $length, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value) && strlen($value) < $length) {
            $this->addError($field, $message ?? ucfirst($field) . " must be at least {$length} characters");
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength($field, $length, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value) && strlen($value) > $length) {
            $this->addError($field, $message ?? ucfirst($field) . " must not exceed {$length} characters");
        }
        return $this;
    }
    
    /**
     * Validate numeric values
     */
    public function numeric($field, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, $message ?? ucfirst($field) . ' must be a number');
        }
        return $this;
    }
    
    /**
     * Validate minimum value
     */
    public function min($field, $min, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value) && is_numeric($value) && $value < $min) {
            $this->addError($field, $message ?? ucfirst($field) . " must be at least {$min}");
        }
        return $this;
    }
    
    /**
     * Validate maximum value
     */
    public function max($field, $max, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value) && is_numeric($value) && $value > $max) {
            $this->addError($field, $message ?? ucfirst($field) . " must not exceed {$max}");
        }
        return $this;
    }
    
    /**
     * Validate that value is in array
     */
    public function in($field, $array, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value) && !in_array($value, $array)) {
            $this->addError($field, $message ?? ucfirst($field) . ' has invalid value');
        }
        return $this;
    }
    
    /**
     * Validate password strength
     */
    public function password($field, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value)) {
            if (strlen($value) < 8) {
                $this->addError($field, $message ?? 'Password must be at least 8 characters');
            } elseif (!preg_match('/[A-Z]/', $value)) {
                $this->addError($field, $message ?? 'Password must contain at least one uppercase letter');
            } elseif (!preg_match('/[a-z]/', $value)) {
                $this->addError($field, $message ?? 'Password must contain at least one lowercase letter');
            } elseif (!preg_match('/[0-9]/', $value)) {
                $this->addError($field, $message ?? 'Password must contain at least one number');
            }
        }
        return $this;
    }
    
    /**
     * Validate phone number (Nepal format)
     */
    public function phone($field, $message = null)
    {
        $value = $this->getValue($field);
        if (!empty($value)) {
            // Nepal phone format: 98xxxxxxxx or 01xxxxxxx
            if (!preg_match('/^(98\d{8}|01\d{7})$/', $value)) {
                $this->addError($field, $message ?? 'Invalid phone number format');
            }
        }
        return $this;
    }
    
    /**
     * Validate file upload
     */
    public function file($field, $allowedExtensions = [], $maxSize = 2097152, $message = null) // 2MB default
    {
        if (isset($_FILES[$field])) {
            $file = $_FILES[$field];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Check file size
                if ($file['size'] > $maxSize) {
                    $this->addError($field, $message ?? 'File size too large. Maximum size: ' . ($maxSize / 1024 / 1024) . 'MB');
                }
                
                // Check file extension
                if (!empty($allowedExtensions)) {
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($extension, $allowedExtensions)) {
                        $this->addError($field, $message ?? 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
                    }
                }
            } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $this->addError($field, $message ?? 'File upload error');
            }
        }
        return $this;
    }
    
    /**
     * Validate that field matches another field
     */
    public function matches($field, $matchField, $message = null)
    {
        $value = $this->getValue($field);
        $matchValue = $this->getValue($matchField);
        
        if ($value !== $matchValue) {
            $this->addError($field, $message ?? ucfirst($field) . ' must match ' . $matchField);
        }
        return $this;
    }
    
    /**
     * Custom validation rule
     */
    public function custom($field, $callback, $message = null)
    {
        $value = $this->getValue($field);
        if (!$callback($value)) {
            $this->addError($field, $message ?? ucfirst($field) . ' is invalid');
        }
        return $this;
    }
    
    /**
     * Get field value
     */
    private function getValue($field)
    {
        return $this->data[$field] ?? '';
    }
    
    /**
     * Add error message
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Check if validation passes
     */
    public function passes()
    {
        return empty($this->errors);
    }
    
    /**
     * Check if validation fails
     */
    public function fails()
    {
        return !$this->passes();
    }
    
    /**
     * Get all errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Get errors for specific field
     */
    public function getError($field)
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Get first error for specific field
     */
    public function getFirstError($field)
    {
        $errors = $this->getError($field);
        return $errors[0] ?? '';
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token)
    {
        session_start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRF()
    {
        session_start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
