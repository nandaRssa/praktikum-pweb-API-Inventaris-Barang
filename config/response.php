<!-- Nanda Raissa (247006111108) -->

<?php

class Response
{
    public static function json(int $code, string $status, string $message, mixed $data = null): void
    {
        http_response_code($code);
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function success(string $message, mixed $data = null, int $code = 200): void
    {
        self::json($code, 'success', $message, $data);
    }

    public static function error(string $message, int $code = 400, mixed $data = null): void
    {
        self::json($code, 'error', $message, $data);
    }

    public static function methodNotAllowed(): void
    {
        self::json(405, 'error', 'Method tidak diizinkan.');
    }

    public static function notFound(string $message = 'Data tidak ditemukan.'): void
    {
        self::json(404, 'error', $message);
    }
}
