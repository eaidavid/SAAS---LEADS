<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    protected string $table;

    public function all(): array
    {
        $stmt = Database::pdo()->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(["id" => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}