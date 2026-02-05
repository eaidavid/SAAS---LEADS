<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;
use App\Models\Lead;

require __DIR__ . "/../vendor/autoload.php";

Env::load(__DIR__ . "/../.env");

$options = getopt("", [
    "project:",
    "mode::",
    "dry-run",
    "commit",
    "include-archived",
    "help",
]);

if (isset($options["help"]) || !isset($options["project"])) {
    echo "Usage: php scripts/dedupe_leads.php --project=\"dentista\" [--mode=delete|archive] [--commit|--dry-run] [--include-archived]\n";
    exit(1);
}

$mode = strtolower((string) ($options["mode"] ?? "delete"));
if (!in_array($mode, ["delete", "archive"], true)) {
    echo "Invalid --mode. Use delete or archive.\n";
    exit(1);
}

$commit = array_key_exists("commit", $options);
$dryRun = array_key_exists("dry-run", $options) || !$commit;
$includeArchived = array_key_exists("include-archived", $options);

$projectArg = trim((string) $options["project"]);
if ($projectArg === "") {
    echo "Project name or id is required.\n";
    exit(1);
}

$pdo = Database::pdo();
$leadModel = new Lead();

$projectId = null;
$projectName = null;
if (ctype_digit($projectArg)) {
    $projectId = (int) $projectArg;
    $stmt = $pdo->prepare("SELECT id, name FROM projects WHERE id = :id LIMIT 1");
    $stmt->execute(["id" => $projectId]);
    $row = $stmt->fetch();
    if ($row !== false) {
        $projectName = (string) ($row["name"] ?? "");
    }
} else {
    $stmt = $pdo->prepare("SELECT id, name FROM projects WHERE LOWER(name) = LOWER(:name)");
    $stmt->execute(["name" => $projectArg]);
    $rows = $stmt->fetchAll();
    if (count($rows) === 1) {
        $projectId = (int) $rows[0]["id"];
        $projectName = (string) ($rows[0]["name"] ?? "");
    } elseif (count($rows) > 1) {
        echo "Multiple projects match '{$projectArg}':\n";
        foreach ($rows as $row) {
            echo "- {$row["id"]}: {$row["name"]}\n";
        }
        exit(1);
    }
}

if ($projectId === null) {
    $stmt = $pdo->prepare("SELECT id, name FROM projects WHERE name LIKE :name ORDER BY name LIMIT 10");
    $stmt->execute(["name" => "%" . $projectArg . "%"]);
    $rows = $stmt->fetchAll();
    if ($rows !== []) {
        echo "No exact project match. Suggestions:\n";
        foreach ($rows as $row) {
            echo "- {$row["id"]}: {$row["name"]}\n";
        }
    } else {
        echo "Project not found.\n";
    }
    exit(1);
}

$stmt = $pdo->prepare("SHOW COLUMNS FROM leads LIKE 'dedupe_key'");
$stmt->execute();
if ($stmt->fetch() === false) {
    if ($dryRun) {
        echo "Warning: dedupe_key column is missing. Dry-run will proceed without writing it.\n";
    } else {
        $pdo->exec("ALTER TABLE leads ADD COLUMN dedupe_key VARCHAR(64) NULL");
    }
}

$sql = "SELECT * FROM leads WHERE project_id = :project_id";
if (!$includeArchived) {
    $sql .= " AND archived_at IS NULL";
}
$stmt = $pdo->prepare($sql);
$stmt->execute(["project_id" => $projectId]);
$leads = $stmt->fetchAll();

if ($leads === []) {
    echo "No leads found for project {$projectId}.\n";
    exit(0);
}

$byId = [];
$dedupeById = [];
$groups = [];
$dedupeCount = 0;

foreach ($leads as $lead) {
    $id = (int) $lead["id"];
    $byId[$id] = $lead;
    $dedupeKey = $leadModel->dedupeKeyFromData($lead);
    if ($dedupeKey !== null && $dedupeKey !== "") {
        $dedupeCount++;
        $dedupeById[$id] = $dedupeKey;
        $groups[$dedupeKey][] = $id;
    }
}

$duplicateGroups = array_filter($groups, fn (array $ids) => count($ids) > 1);
$duplicateCount = 0;
foreach ($duplicateGroups as $ids) {
    $duplicateCount += count($ids) - 1;
}

$winnerOriginals = [];
$winnerMerged = [];
$deleteMap = [];

foreach ($duplicateGroups as $dedupeKey => $ids) {
    $winnerId = pickWinner($ids, $byId);
    $winner = $byId[$winnerId];
    $winnerOriginals[$winnerId] = $winner;

    foreach ($ids as $id) {
        if ($id === $winnerId) {
            continue;
        }

        $winner = mergeLead($winner, $byId[$id]);
        $deleteMap[$id] = $winnerId;
    }

    $byId[$winnerId] = $winner;
    $winnerMerged[$winnerId] = true;
}

$leadUpdates = [];
foreach ($byId as $id => $lead) {
    if (isset($deleteMap[$id])) {
        continue;
    }

    $data = [];
    $dedupeKey = $leadModel->dedupeKeyFromData($lead);
    if ($dedupeKey !== null && $dedupeKey !== ($lead["dedupe_key"] ?? null)) {
        $data["dedupe_key"] = $dedupeKey;
    }

    if (isset($winnerMerged[$id])) {
        $original = $winnerOriginals[$id] ?? [];
        foreach (mergeFields() as $field) {
            if (array_key_exists($field, $lead) && (!array_key_exists($field, $original) || $lead[$field] !== $original[$field])) {
                $data[$field] = $lead[$field];
            }
        }
    }

    if ($data !== []) {
        $leadUpdates[$id] = $data;
    }
}

echo "Project: {$projectId}" . ($projectName !== null ? " ({$projectName})" : "") . "\n";
echo "Leads scanned: " . count($leads) . "\n";
echo "Leads with dedupe key: {$dedupeCount}\n";
echo "Duplicate groups: " . count($duplicateGroups) . "\n";
echo "Duplicates to " . ($mode === "delete" ? "delete" : "archive") . ": {$duplicateCount}\n";

if ($dryRun) {
    echo "Dry-run only. Re-run with --commit to apply changes.\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    foreach ($leadUpdates as $id => $data) {
        $leadModel->update($id, $data);
    }

    $now = date("Y-m-d H:i:s");
    foreach ($deleteMap as $dupId => $winnerId) {
        $stmt = $pdo->prepare("UPDATE interactions SET lead_id = :winner_id WHERE lead_id = :dup_id");
        $stmt->execute(["winner_id" => $winnerId, "dup_id" => $dupId]);

        $stmt = $pdo->prepare("UPDATE proposals SET lead_id = :winner_id WHERE lead_id = :dup_id");
        $stmt->execute(["winner_id" => $winnerId, "dup_id" => $dupId]);

        $stmt = $pdo->prepare("UPDATE contracts SET lead_id = :winner_id WHERE lead_id = :dup_id");
        $stmt->execute(["winner_id" => $winnerId, "dup_id" => $dupId]);

        if ($mode === "archive") {
            $stmt = $pdo->prepare("UPDATE leads SET archived_at = :archived_at WHERE id = :id");
            $stmt->execute(["archived_at" => $now, "id" => $dupId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM leads WHERE id = :id");
            $stmt->execute(["id" => $dupId]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

echo "Done. Updated leads: " . count($leadUpdates) . "\n";

function pickWinner(array $ids, array $byId): int
{
    $bestId = $ids[0];
    $bestScore = completenessScore($byId[$bestId]);

    foreach ($ids as $id) {
        $score = completenessScore($byId[$id]);
        if ($score > $bestScore) {
            $bestId = $id;
            $bestScore = $score;
            continue;
        }

        if ($score === $bestScore) {
            $bestCreated = strtotime((string) ($byId[$bestId]["created_at"] ?? "")) ?: PHP_INT_MAX;
            $candidateCreated = strtotime((string) ($byId[$id]["created_at"] ?? "")) ?: PHP_INT_MAX;
            if ($candidateCreated < $bestCreated) {
                $bestId = $id;
                $bestScore = $score;
                continue;
            }

            if ($candidateCreated === $bestCreated && $id < $bestId) {
                $bestId = $id;
                $bestScore = $score;
            }
        }
    }

    return $bestId;
}

function completenessScore(array $lead): int
{
    $fields = [
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
        "tags",
        "notes",
    ];

    $score = 0;
    foreach ($fields as $field) {
        if (!isEmptyValue($lead[$field] ?? null)) {
            $score++;
        }
    }

    return $score;
}

function mergeLead(array $winner, array $dup): array
{
    foreach (mergeFields() as $field) {
        if ($field === "tags") {
            $merged = mergeTags($winner["tags"] ?? null, $dup["tags"] ?? null);
            if ($merged !== null) {
                $winner["tags"] = $merged;
            }
            continue;
        }

        if (isEmptyValue($winner[$field] ?? null) && !isEmptyValue($dup[$field] ?? null)) {
            $winner[$field] = $dup[$field];
        }
    }

    return $winner;
}

function mergeFields(): array
{
    return [
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
        "tags",
        "notes",
        "imported_at",
    ];
}

function mergeTags(mixed $winner, mixed $dup): ?string
{
    $winnerTags = normalizeTags($winner);
    $dupTags = normalizeTags($dup);
    $merged = array_values(array_unique(array_merge($winnerTags, $dupTags)));
    if ($merged === []) {
        return null;
    }

    return json_encode($merged, JSON_UNESCAPED_UNICODE);
}

function normalizeTags(mixed $value): array
{
    if ($value === null || $value === "") {
        return [];
    }

    if (is_array($value)) {
        return array_values(array_unique(array_map("trim", $value)));
    }

    $decoded = json_decode((string) $value, true);
    if (is_array($decoded)) {
        return array_values(array_unique(array_map("trim", $decoded)));
    }

    $raw = array_map("trim", explode(",", (string) $value));
    return array_values(array_filter($raw, fn ($item) => $item !== ""));
}

function isEmptyValue(mixed $value): bool
{
    if ($value === null) {
        return true;
    }

    if (is_string($value)) {
        return trim($value) === "";
    }

    return false;
}
