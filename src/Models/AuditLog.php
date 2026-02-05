<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AuditLog extends BaseModel
{
    protected string $table = "audit_logs";

    public function record(string $entityType, int $entityId, string $action, ?string $message = null): void
    {
        $sql = "INSERT INTO {$this->table} (entity_type, entity_id, action, message) VALUES (:entity_type, :entity_id, :action, :message)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            "entity_type" => $entityType,
            "entity_id" => $entityId,
            "action" => $action,
            "message" => $message,
        ]);
    }
}
