<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Request;

final class ServiceController
{
    public function index(Request $request): void
    {
        View::render("services/index");
    }
}