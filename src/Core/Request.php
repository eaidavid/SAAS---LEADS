<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public string $method;
    public string $path;
    public array $query;
    public array $body;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER["REQUEST_METHOD"] ?? "GET");
        $uri = $_SERVER["REQUEST_URI"] ?? "/";
        $this->path = parse_url($uri, PHP_URL_PATH) ?? "/";
        $this->query = $_GET ?? [];
        $this->body = $_POST ?? [];
    }
}