<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode(".", $key);
        $file = array_shift($parts);
        if ($file === null || $file === "") {
            return $default;
        }

        $path = dirname(__DIR__, 2) . "/config/" . $file . ".php";
        if (!is_file($path)) {
            return $default;
        }

        $data = require $path;
        foreach ($parts as $part) {
            if (!is_array($data) || !array_key_exists($part, $data)) {
                return $default;
            }
            $data = $data[$part];
        }

        return $data;
    }
}
