<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Request;

final class ProposalController
{
    public function index(Request $request): void
    {
        View::render("proposals/index");
    }
}