<?php
/**
 * Lightweight API response helper utilities.
 */
namespace Doko\Http;

class ApiResponse {
    private static array $defaultHeaders = [
        'Content-Type' => 'application/json; charset=utf-8'
    ];

    // Simple allow‑list for CORS; adjust as needed.
    private static array $allowedOrigins = [
        'http://localhost',
        'http://127.0.0.1',
    ];

    public static function send(array $payload, int $status = 200, array $headers = []): void {
        http_response_code($status);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, self::$allowedOrigins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        foreach (self::$defaultHeaders as $k => $v) {
            if (!headers_sent()) header($k . ': ' . $v, true);
        }
        foreach ($headers as $k => $v) {
            header($k . ': ' . $v, true);
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public static function success(array $data = [], int $status = 200): void {
        self::send(['success' => true] + $data, $status);
    }

    public static function error(string $message, int $status = 400, array $extra = []): void {
        self::send(['success' => false, 'message' => $message] + $extra, $status);
    }
}
?>
