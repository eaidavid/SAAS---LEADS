<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Interaction extends BaseModel
{
    protected string $table = "interactions";

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (lead_id, user_id, type, message, date) VALUES (:lead_id, :user_id, :type, :message, :date)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            "lead_id" => $data["lead_id"],
            "user_id" => $data["user_id"],
            "type" => $data["type"],
            "message" => $data["message"],
            "date" => $data["date"],
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public function byLead(int $leadId): array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM {$this->table} WHERE lead_id = :lead_id ORDER BY created_at DESC");
        $stmt->execute(["lead_id" => $leadId]);
        return $stmt->fetchAll();
    }
}
