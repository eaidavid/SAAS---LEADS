<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function run(): void
    {
        session_start();

        $routes = require dirname(__DIR__, 2) . "/config/routes.php";
        $routes($this->router);

        $this->router->dispatch(new Request());
    }
}