<?php
declare(strict_types=1);

// Serve static assets when running behind a single PHP entrypoint (e.g. Vercel).
$requestPath = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH) ?? "/";
if (str_starts_with($requestPath, "/assets/")) {
    $assetRoot = realpath(__DIR__ . "/assets");
    $assetPath = realpath(__DIR__ . $requestPath);
    if ($assetRoot !== false && $assetPath !== false && str_starts_with($assetPath, $assetRoot) && is_file($assetPath)) {
        $ext = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        $types = [
            "css" => "text/css; charset=utf-8",
            "js" => "application/javascript; charset=utf-8",
            "svg" => "image/svg+xml",
            "png" => "image/png",
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "webp" => "image/webp",
            "gif" => "image/gif",
            "ico" => "image/x-icon",
            "woff" => "font/woff",
            "woff2" => "font/woff2",
            "ttf" => "font/ttf",
        ];
        if (isset($types[$ext])) {
            header("Content-Type: " . $types[$ext]);
        }
        header("Cache-Control: public, max-age=31536000, immutable");
        readfile($assetPath);
        exit;
    }

    http_response_code(404);
    exit;
}

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Core\Env;
use App\Core\App;

Env::load(dirname(__DIR__) . "/.env");

$app = new App();
$app->run();
