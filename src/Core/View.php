<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewFile = dirname(__DIR__, 2) . "/views/" . $view . ".php";
        if (!is_file($viewFile)) {
            Response::notFound();
            return;
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require dirname(__DIR__, 2) . "/views/layouts/app.php";
    }
}