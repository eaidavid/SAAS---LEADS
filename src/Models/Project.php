<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Project extends BaseModel
{
    protected string $table = "projects";

    public function listWithCounts(): array
    {
        $sql = "SELECT p.*, COUNT(DISTINCT i.id) AS imports_count, COUNT(l.id) AS leads_count
                FROM projects p
                LEFT JOIN imports i ON i.project_id = p.id AND i.archived_at IS NULL
                LEFT JOIN leads l ON l.project_id = p.id AND l.archived_at IS NULL
                WHERE p.archived_at IS NULL
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        $stmt = Database::pdo()->query($sql);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, niche, description) VALUES (:name, :niche, :description)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            "name" => $data["name"],
            "niche" => $data["niche"] ?? null,
            "description" => $data["description"] ?? null,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = "UPDATE {$this->table} SET name = :name, niche = :niche, description = :description WHERE id = :id";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            "id" => $id,
            "name" => $data["name"],
            "niche" => $data["niche"] ?? null,
            "description" => $data["description"] ?? null,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(["id" => $id]);
    }

    public function archive(int $id): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = :archived_at WHERE id = :id");
        $stmt->execute([
            "id" => $id,
            "archived_at" => date("Y-m-d H:i:s"),
        ]);
    }

    public function restore(int $id): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = NULL WHERE id = :id");
        $stmt->execute(["id" => $id]);
    }

    public function listArchived(): array
    {
        $sql = "SELECT p.*, COUNT(DISTINCT i.id) AS imports_count, COUNT(l.id) AS leads_count
                FROM projects p
                LEFT JOIN imports i ON i.project_id = p.id
                LEFT JOIN leads l ON l.project_id = p.id
                WHERE p.archived_at IS NOT NULL
                GROUP BY p.id
                ORDER BY p.archived_at DESC";
        $stmt = Database::pdo()->query($sql);
        return $stmt->fetchAll();
    }
}
