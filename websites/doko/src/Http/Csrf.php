<?php
/**
 * Minimal CSRF token helper for API endpoints.
 */
namespace Doko\Http;

class Csrf {
    public const HEADER = 'HTTP_X_CSRF_TOKEN';

    public static function ensureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function token(): string {
        self::ensureSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(?string $token): bool {
        self::ensureSession();
        return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireValid(?string $token): void {
        if (!self::validate($token)) {
            http_response_code(419); // Authentication Timeout
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token']);
            exit;
        }
    }
}
?>
