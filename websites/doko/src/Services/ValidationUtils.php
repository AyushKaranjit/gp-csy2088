<?php
/**
 * DOKO E-Commerce Website - Validation Utilities
 *
 * This file contains validation functions used across the application
 * for consistent data validation and security.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
 */

class ValidationUtils {
    /**
     * Validate email address
     */
    public static function validateEmail($email) {
        if (!is_string($email)) return false;
        $email = trim($email);
        if (empty($email)) return false;

        // Basic format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        // Length check
        if (strlen($email) > 100) return false;

        // Domain validation
        $domain = substr(strrchr($email, "@"), 1);
        if (empty($domain)) return false;

        // Check for disposable email domains
        $disposableDomains = [
            '10minutemail.com', 'guerrillamail.com', 'mailinator.com',
            'temp-mail.org', 'throwaway.email', 'yopmail.com'
        ];

        if (in_array(strtolower($domain), $disposableDomains)) return false;

        return true;
    }

    /**
     * Validate Nepali phone number
     */
    public static function validatePhone($phone) {
        if (!is_string($phone)) return false;
        $phone = trim($phone);

        // Allow empty phone (optional field)
        if (empty($phone)) return true;

        // Remove all non-digit characters except +
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

        // Check various Nepali phone number formats
        $patterns = [
            '/^\+977[0-9]{10}$/',  // +977XXXXXXXXXX
            '/^977[0-9]{10}$/',    // 977XXXXXXXXXX
            '/^0[0-9]{9}$/',       // 0XXXXXXXXX
            '/^[0-9]{10}$/'        // XXXXXXXXXX
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanPhone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        if (!is_string($password)) return ['valid' => false, 'message' => 'Password must be a string'];

        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'one lowercase letter';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'one number';
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'one special character';
        }

        if (empty($errors)) {
            return ['valid' => true, 'strength' => 'strong', 'message' => 'Password is strong'];
        } else {
            $message = 'Password must contain ' . implode(', ', $errors);
            $strength = count($errors) <= 2 ? 'good' : (count($errors) <= 3 ? 'fair' : 'weak');
            return ['valid' => false, 'strength' => $strength, 'message' => $message];
        }
    }

    /**
     * Validate name (first/last name)
     */
    public static function validateName($name, $field = 'Name') {
        if (!is_string($name)) return ['valid' => false, 'message' => $field . ' must be a string'];

        $name = trim($name);

        if (empty($name)) {
            return ['valid' => false, 'message' => $field . ' is required'];
        }

        if (strlen($name) < 2) {
            return ['valid' => false, 'message' => $field . ' must be at least 2 characters long'];
        }

        if (strlen($name) > 50) {
            return ['valid' => false, 'message' => $field . ' must not exceed 50 characters'];
        }

        // Allow letters, spaces, hyphens, and apostrophes
        if (!preg_match("/^[a-zA-Z\s\-']+$/", $name)) {
            return ['valid' => false, 'message' => $field . ' can only contain letters, spaces, hyphens, and apostrophes'];
        }

        return ['valid' => true, 'message' => 'Valid ' . strtolower($field)];
    }

    /**
     * Validate address
     */
    public static function validateAddress($address) {
        if (!is_string($address)) return ['valid' => false, 'message' => 'Address must be a string'];

        $address = trim($address);

        if (empty($address)) {
            return ['valid' => false, 'message' => 'Address is required'];
        }

        if (strlen($address) < 10) {
            return ['valid' => false, 'message' => 'Address must be at least 10 characters long'];
        }

        if (strlen($address) > 200) {
            return ['valid' => false, 'message' => 'Address must not exceed 200 characters'];
        }

        return ['valid' => true, 'message' => 'Valid address'];
    }

    /**
     * Validate area/locality
     */
    public static function validateArea($area) {
        if (!is_string($area)) return ['valid' => false, 'message' => 'Area must be a string'];

        $area = trim($area);

        if (empty($area)) {
            return ['valid' => false, 'message' => 'Area is required'];
        }

        if (strlen($area) < 3) {
            return ['valid' => false, 'message' => 'Area must be at least 3 characters long'];
        }

        if (strlen($area) > 50) {
            return ['valid' => false, 'message' => 'Area must not exceed 50 characters'];
        }

        return ['valid' => true, 'message' => 'Valid area'];
    }

    /**
     * Validate message/content
     */
    public static function validateMessage($message, $minLength = 10, $maxLength = 1000) {
        if (!is_string($message)) return ['valid' => false, 'message' => 'Message must be a string'];

        $message = trim($message);

        if (empty($message)) {
            return ['valid' => false, 'message' => 'Message is required'];
        }

        if (strlen($message) < $minLength) {
            return ['valid' => false, 'message' => "Message must be at least {$minLength} characters long"];
        }

        if (strlen($message) > $maxLength) {
            return ['valid' => false, 'message' => "Message must not exceed {$maxLength} characters"];
        }

        return ['valid' => true, 'message' => 'Valid message'];
    }

    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }

        if (is_string($data)) {
            // Remove null bytes
            $data = str_replace("\0", '', $data);
            // Trim whitespace
            $data = trim($data);
            // Convert special characters to HTML entities
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }

        return $data;
    }

    /**
     * Validate date format and range
     */
    public static function validateDate($date, $minDate = null, $maxDate = null) {
        if (!is_string($date)) return ['valid' => false, 'message' => 'Date must be a string'];

        $date = trim($date);

        if (empty($date)) {
            return ['valid' => false, 'message' => 'Date is required'];
        }

        // Check date format (YYYY-MM-DD)
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $date) {
            return ['valid' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD'];
        }

        // Check minimum date
        if ($minDate) {
            $minDateTime = new DateTime($minDate);
            if ($dateTime < $minDateTime) {
                return ['valid' => false, 'message' => 'Date cannot be before ' . $minDate];
            }
        }

        // Check maximum date
        if ($maxDate) {
            $maxDateTime = new DateTime($maxDate);
            if ($dateTime > $maxDateTime) {
                return ['valid' => false, 'message' => 'Date cannot be after ' . $maxDate];
            }
        }

        return ['valid' => true, 'message' => 'Valid date'];
    }

    /**
     * Validate time slot
     */
    public static function validateTimeSlot($timeSlot) {
        $validSlots = [
            'morning', 'afternoon', 'evening',
            '08:00-12:00', '12:00-17:00', '17:00-21:00'
        ];

        if (!in_array($timeSlot, $validSlots)) {
            return ['valid' => false, 'message' => 'Invalid time slot selected'];
        }

        return ['valid' => true, 'message' => 'Valid time slot'];
    }

    /**
     * Validate payment method
     */
    public static function validatePaymentMethod($method) {
        $validMethods = ['cod', 'esewa', 'khalti', 'cash_on_delivery'];

        if (!in_array($method, $validMethods)) {
            return ['valid' => false, 'message' => 'Invalid payment method'];
        }

        return ['valid' => true, 'message' => 'Valid payment method'];
    }

    /**
     * Validate quantity
     */
    public static function validateQuantity($quantity, $max = 100) {
        if (!is_numeric($quantity)) {
            return ['valid' => false, 'message' => 'Quantity must be a number'];
        }

        $quantity = (int)$quantity;

        if ($quantity < 1) {
            return ['valid' => false, 'message' => 'Quantity must be at least 1'];
        }

        if ($quantity > $max) {
            return ['valid' => false, 'message' => "Quantity cannot exceed {$max}"];
        }

        return ['valid' => true, 'message' => 'Valid quantity'];
    }

    /**
     * Validate price
     */
    public static function validatePrice($price, $min = 0, $max = 100000) {
        if (!is_numeric($price)) {
            return ['valid' => false, 'message' => 'Price must be a number'];
        }

        $price = (float)$price;

        if ($price < $min) {
            return ['valid' => false, 'message' => "Price cannot be less than {$min}"];
        }

        if ($price > $max) {
            return ['valid' => false, 'message' => "Price cannot exceed {$max}"];
        }

        return ['valid' => true, 'message' => 'Valid price'];
    }
}
?>
