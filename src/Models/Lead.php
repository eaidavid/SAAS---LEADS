<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Lead extends BaseModel
{
    protected string $table = "leads";

    private array $fields = [
        "project_id",
        "import_id",
        "place_id",
        "name",
        "phone",
        "mobile",
        "email",
        "website",
        "address",
        "city",
        "state",
        "latitude",
        "longitude",
        "category",
        "google_maps_url",
        "comments",
        "rating",
        "reviews_count",
        "status",
        "score",
        "tags",
        "notes",
        "dedupe_key",
        "imported_at",
    ];

    public function create(array $data): int
    {
        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($this->fields as $field) {
            if (array_key_exists($field, $data)) {
                $columns[] = $field;
                $placeholders[] = ":" . $field;
                $params[$field] = $data[$field];
            }
        }

        if ($columns === []) {
            return 0;
        }

        $sql = "INSERT INTO {$this->table} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);

        return (int) Database::pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];
        $params = ["id" => $id];

        foreach ($this->fields as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = $field . " = :" . $field;
                $params[$field] = $data[$field];
            }
        }

        if ($sets === []) {
            return;
        }

        $sql = "UPDATE {$this->table} SET " . implode(", ", $sets) . " WHERE id = :id";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
    }

    public function createIfNotExists(array $data): int
    {
        $dedupeKey = $data["dedupe_key"] ?? $this->dedupeKeyFromData($data);
        if (is_string($dedupeKey) && $dedupeKey !== "") {
            $data["dedupe_key"] = $dedupeKey;
            $projectId = is_numeric($data["project_id"] ?? null) ? (int) $data["project_id"] : null;
            if ($this->existsByDedupeKey($projectId, $dedupeKey)) {
                return 0;
            }
        } else {
            unset($data["dedupe_key"]);
        }

        try {
            return $this->create($data);
        } catch (\PDOException $e) {
            if ($this->isDuplicateException($e)) {
                return 0;
            }

            throw $e;
        }
    }

    public function dedupeKeyFromData(array $data): ?string
    {
        $base = $this->dedupeBase($data);
        if ($base === "") {
            return null;
        }

        return sha1($base);
    }

    public function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(["id" => $id]);
    }

    public function deleteByProject(int $projectId): void
    {
        $stmt = Database::pdo()->prepare("DELETE FROM {$this->table} WHERE project_id = :project_id");
        $stmt->execute(["project_id" => $projectId]);
    }

    public function deleteByImport(int $importId): void
    {
        $stmt = Database::pdo()->prepare("DELETE FROM {$this->table} WHERE import_id = :import_id");
        $stmt->execute(["import_id" => $importId]);
    }

    public function archiveByProject(int $projectId): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = :archived_at WHERE project_id = :project_id");
        $stmt->execute([
            "project_id" => $projectId,
            "archived_at" => date("Y-m-d H:i:s"),
        ]);
    }

    public function archiveByImport(int $importId): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = :archived_at WHERE import_id = :import_id");
        $stmt->execute([
            "import_id" => $importId,
            "archived_at" => date("Y-m-d H:i:s"),
        ]);
    }

    public function restoreByProject(int $projectId): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = NULL WHERE project_id = :project_id");
        $stmt->execute(["project_id" => $projectId]);
    }

    public function restoreByImport(int $importId): void
    {
        $stmt = Database::pdo()->prepare("UPDATE {$this->table} SET archived_at = NULL WHERE import_id = :import_id");
        $stmt->execute(["import_id" => $importId]);
    }

    public function search(array $filters): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        $projectId = $filters["project_id"] ?? null;
        if (is_numeric($projectId)) {
            $sql .= " AND project_id = :project_id";
            $params["project_id"] = (int) $projectId;
        }

        $importId = $filters["import_id"] ?? null;
        if (is_numeric($importId)) {
            $sql .= " AND import_id = :import_id";
            $params["import_id"] = (int) $importId;
        }

        $status = $filters["status"] ?? null;
        if (is_string($status) && $status !== "") {
            $sql .= " AND status = :status";
            $params["status"] = $status;
        }

        $category = $filters["category"] ?? null;
        if (is_string($category) && $category !== "") {
            $sql .= " AND category LIKE :category";
            $params["category"] = "%" . $category . "%";
        }

        $minRating = $filters["min_rating"] ?? null;
        if (is_numeric($minRating)) {
            $sql .= " AND rating >= :min_rating";
            $params["min_rating"] = (float) $minRating;
        }

        $query = $filters["q"] ?? null;
        if (is_string($query) && $query !== "") {
            $sql .= " AND (name LIKE :q OR city LIKE :q OR phone LIKE :q OR mobile LIKE :q OR email LIKE :q OR category LIKE :q OR address LIKE :q OR website LIKE :q OR comments LIKE :q)";
            $params["q"] = "%" . $query . "%";
        }

        $orderBy = $filters["order_by"] ?? "created_at";
        $direction = strtoupper((string) ($filters["direction"] ?? "DESC"));
        $allowed = [
            "created_at",
            "imported_at",
            "name",
            "category",
            "rating",
            "reviews_count",
            "city",
            "state",
            "status",
            "score",
        ];
        if (!in_array($orderBy, $allowed, true)) {
            $orderBy = "created_at";
        }
        $direction = $direction === "ASC" ? "ASC" : "DESC";
        $sql .= " ORDER BY {$orderBy} {$direction}";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countByStatus(array $filters = []): array
    {
        $sql = "SELECT status, COUNT(*) AS total FROM {$this->table} WHERE 1=1";
        $params = [];

        $projectId = $filters["project_id"] ?? null;
        if (is_numeric($projectId)) {
            $sql .= " AND project_id = :project_id";
            $params["project_id"] = (int) $projectId;
        }

        $importId = $filters["import_id"] ?? null;
        if (is_numeric($importId)) {
            $sql .= " AND import_id = :import_id";
            $params["import_id"] = (int) $importId;
        }

        $sql .= " GROUP BY status";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row["status"]] = (int) $row["total"];
        }

        return $counts;
    }

    private function existsByDedupeKey(?int $projectId, string $dedupeKey): bool
    {
        $driver = (string) Database::pdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $comparison = $driver === "pgsql" ? "IS NOT DISTINCT FROM" : "<=>";
        $stmt = Database::pdo()->prepare("SELECT id FROM {$this->table} WHERE project_id {$comparison} :project_id AND dedupe_key = :dedupe_key LIMIT 1");
        $stmt->execute([
            "project_id" => $projectId,
            "dedupe_key" => $dedupeKey,
        ]);

        return $stmt->fetch() !== false;
    }

    private function isDuplicateException(\PDOException $e): bool
    {
        $sqlState = (string) $e->getCode();
        $driverCode = $e->errorInfo[1] ?? null;
        return $sqlState === "23000" || $sqlState === "23505" || $driverCode === 1062;
    }

    private function dedupeBase(array $data): string
    {
        $placeId = $this->normalizeText($data["place_id"] ?? "");
        if ($placeId !== "") {
            return "place:" . $placeId;
        }

        $mapsUrl = $this->normalizeUrl($data["google_maps_url"] ?? "");
        if ($mapsUrl !== "") {
            return "maps:" . $mapsUrl;
        }

        $email = strtolower(trim((string) ($data["email"] ?? "")));
        if ($email !== "") {
            return "email:" . $email;
        }

        $phone = $this->normalizePhone($data["mobile"] ?? "");
        if ($phone === "") {
            $phone = $this->normalizePhone($data["phone"] ?? "");
        }

        if ($phone !== "") {
            return "phone:" . $phone;
        }

        $name = $this->normalizeText($data["name"] ?? "");
        $address = $this->normalizeText($data["address"] ?? "");
        $city = $this->normalizeText($data["city"] ?? "");
        $state = $this->normalizeText($data["state"] ?? "");

        if ($name !== "" && ($address !== "" || $city !== "" || $state !== "")) {
            return "nameaddr:" . $name . "|" . $address . "|" . $city . "|" . $state;
        }

        return "";
    }

    private function normalizePhone(mixed $value): string
    {
        $digits = preg_replace("/\\D+/", "", (string) ($value ?? ""));
        return $digits ?? "";
    }

    private function normalizeUrl(mixed $value): string
    {
        $url = trim((string) ($value ?? ""));
        if ($url === "") {
            return "";
        }

        $url = strtolower($url);
        $url = preg_replace("/^https?:\\/\\/(www\\.)?/", "", $url);
        $url = rtrim($url, "/");
        return $url ?? "";
    }

    private function normalizeText(mixed $value): string
    {
        $text = trim((string) ($value ?? ""));
        if ($text === "") {
            return "";
        }

        $text = strtolower($text);
        $text = preg_replace("/[^\\p{L}\\p{N}]+/u", " ", $text);
        $text = preg_replace("/\\s+/", " ", $text);
        return trim($text ?? "");
    }
}
