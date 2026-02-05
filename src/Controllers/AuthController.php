<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Request;
use App\Core\Response;

final class AuthController
{
    public function loginForm(Request $request): void
    {
        View::render("auth/login");
    }

    public function login(Request $request): void
    {
        Response::redirect("/");
    }
}