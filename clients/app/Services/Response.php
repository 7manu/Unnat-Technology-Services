<?php

namespace App\Services;

final class Response
{
    public static function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }
}
