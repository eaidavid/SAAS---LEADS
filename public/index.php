<?php
declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Core\Env;
use App\Core\App;

Env::load(dirname(__DIR__) . "/.env");

$app = new App();
$app->run();