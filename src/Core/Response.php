<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function redirect(string $path): void
    {
        header("Location: " . $path);
        exit;
    }

    public static function notFound(): void
    {
        http_response_code(404);
        View::render("errors/404");
    }
}