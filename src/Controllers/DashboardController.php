<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Request;

final class DashboardController
{
    public function index(Request $request): void
    {
        View::render("dashboard/index", [
            "metrics" => [
                "leads" => 0,
                "proposals" => 0,
                "conversion" => 0,
            ],
        ]);
    }
}