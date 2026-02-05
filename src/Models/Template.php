<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Template extends BaseModel
{
    protected string $table = "templates";

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (type, name, content, variables, active) VALUES (:type, :name, :content, :variables, :active)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            "type" => $data["type"],
            "name" => $data["name"],
            "content" => $data["content"],
            "variables" => $data["variables"] ?? null,
            "active" => $data["active"] ?? 1,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public function allByType(string $type): array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM {$this->table} WHERE type = :type ORDER BY id DESC");
        $stmt->execute(["type" => $type]);
        return $stmt->fetchAll();
    }
}
