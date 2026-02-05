<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = strtolower((string) Env::get("DB_DRIVER", "mysql"));
        if ($driver === "postgres" || $driver === "postgresql") {
            $driver = "pgsql";
        }
        $host = Env::get("DB_HOST", "127.0.0.1");
        $port = Env::get("DB_PORT", "3306");
        $db = Env::get("DB_DATABASE", "saas_leads");
        $user = Env::get("DB_USERNAME", "root");
        $pass = Env::get("DB_PASSWORD", "");

        if ($driver === "pgsql") {
            $sslMode = Env::get("DB_SSLMODE", "require");
            $dsn = sprintf("%s:host=%s;port=%s;dbname=%s;sslmode=%s", $driver, $host, $port, $db, $sslMode);
        } else {
            $dsn = sprintf("%s:host=%s;port=%s;dbname=%s;charset=utf8mb4", $driver, $host, $port, $db);
        }

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw $e;
        }

        return self::$pdo;
    }
}
