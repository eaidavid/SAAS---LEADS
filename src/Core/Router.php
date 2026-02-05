<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [
        "GET" => [],
        "POST" => [],
    ];

    public function get(string $path, $handler): void
    {
        $this->routes["GET"][$path] = $handler;
    }

    public function post(string $path, $handler): void
    {
        $this->routes["POST"][$path] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $handler = $this->routes[$request->method][$request->path] ?? null;
        if ($handler === null) {
            Response::notFound();
            return;
        }

        if (is_callable($handler)) {
            $handler($request);
            return;
        }

        if (is_string($handler) && str_contains($handler, "@")) {
            [$controllerName, $method] = explode("@", $handler, 2);
            $class = "App\\Controllers\\" . $controllerName;
            if (!class_exists($class)) {
                Response::notFound();
                return;
            }

            $controller = new $class();
            if (!method_exists($controller, $method)) {
                Response::notFound();
                return;
            }

            $controller->$method($request);
            return;
        }

        Response::notFound();
    }
}