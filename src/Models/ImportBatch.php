<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ImportBatch extends BaseModel
{
    protected string $table = "imports";

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (project_id, name, source_filename, total_rows, imported_rows, imported_at) VALUES (:project_id, :name, :source_filename, :total_rows, :imported_rows, :imported_at)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            "project_id" => $data["project_id"],
            "name" => $data["name"],
            "source_filename" => $data["source_filename"] ?? null,
            "total_rows" => $data["total_rows"] ?? 0,
            "imported_rows" => $data["imported_rows"] ?? 0,
            "imported_at" => $data["imported_at"] ?? null,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public function updateCounts(int $id, int $total, int $imported): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET total_rows = :total, imported_rows = :imported WHERE id = :id");
        $stmt->execute([
            "id" => $id,
            "total" => $total,
            "imported" => $imported,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $sql = "UPDATE {$this->table} SET name = :name WHERE id = :id";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            "id" => $id,
            "name" => $data["name"],
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

    public function archivedByProject(int $projectId): array
    {
        $sql = "SELECT i.*, COUNT(l.id) AS lead_count
                FROM {$this->table} i
                LEFT JOIN leads l ON l.import_id = i.id
                WHERE i.project_id = :project_id
                  AND i.archived_at IS NOT NULL
                GROUP BY i.id
                ORDER BY i.archived_at DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute(["project_id" => $projectId]);
        return $stmt->fetchAll();
    }

    public function deleteByProject(int $projectId): void
    {
        $stmt = Database::pdo()->prepare("DELETE FROM {$this->table} WHERE project_id = :project_id");
        $stmt->execute(["project_id" => $projectId]);
    }

    public function archiveByProject(int $projectId): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = :archived_at WHERE project_id = :project_id");
        $stmt->execute([
            "project_id" => $projectId,
            "archived_at" => date("Y-m-d H:i:s"),
        ]);
    }

    public function restoreByProject(int $projectId): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = NULL WHERE project_id = :project_id");
        $stmt->execute(["project_id" => $projectId]);
    }

    public function byProject(int $projectId): array
    {
        $sql = "SELECT i.*, COUNT(l.id) AS lead_count
                FROM {$this->table} i
                LEFT JOIN leads l ON l.import_id = i.id AND l.archived_at IS NULL
                WHERE i.project_id = :project_id
                  AND i.archived_at IS NULL
                GROUP BY i.id
                ORDER BY i.created_at DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute(["project_id" => $projectId]);
        return $stmt->fetchAll();
    }
}
