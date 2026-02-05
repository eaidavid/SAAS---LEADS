<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\ImportBatch;
use App\Models\Lead;
use App\Models\Project;

final class ImportController
{
    private ImportBatch $imports;
    private Project $projects;
    private Lead $leads;
    private AuditLog $audit;

    public function __construct()
    {
        $this->imports = new ImportBatch();
        $this->projects = new Project();
        $this->leads = new Lead();
        $this->audit = new AuditLog();
    }

    public function create(Request $request): void
    {
        $projectId = (int) ($request->query["project_id"] ?? 0);
        if ($projectId <= 0) {
            Response::redirect("/projects");
            return;
        }

        $project = $this->projects->find($projectId);
        if ($project === null) {
            Response::notFound();
            return;
        }

        View::render("imports/create", [
            "project" => $project,
        ]);
    }

    public function store(Request $request): void
    {
        $projectId = (int) ($request->body["project_id"] ?? 0);
        if ($projectId <= 0) {
            Response::redirect("/projects");
            return;
        }

        $project = $this->projects->find($projectId);
        if ($project === null) {
            Response::redirect("/projects");
            return;
        }

        if (!isset($_FILES["csv"]) || $_FILES["csv"]["error"] !== UPLOAD_ERR_OK) {
            Response::redirect("/imports/new?project_id=" . $projectId);
            return;
        }

        $importName = trim((string) ($request->body["name"] ?? ""));
        if ($importName === "") {
            $importName = "Import " . date("Y-m-d H:i");
        }

        $importId = $this->imports->create([
            "project_id" => $projectId,
            "name" => $importName,
            "source_filename" => $_FILES["csv"]["name"] ?? null,
            "imported_at" => date("Y-m-d H:i:s"),
        ]);

        $handle = fopen($_FILES["csv"]["tmp_name"], "r");
        if ($handle === false) {
            Response::redirect("/projects/show?id=" . $projectId);
            return;
        }

        $total = 0;
        $imported = 0;
        $seen = [];
        $firstRow = fgetcsv($handle, 0, ",");
        if ($firstRow === false) {
            fclose($handle);
            Response::redirect("/projects/show?id=" . $projectId);
            return;
        }

        $hasHeader = $this->isHeaderRow($firstRow);
        if (!$hasHeader) {
            $this->processRow($firstRow, $projectId, $importId, $project, $imported, $total, $seen);
        }

        while (($row = fgetcsv($handle, 0, ",")) !== false) {
            $this->processRow($row, $projectId, $importId, $project, $imported, $total, $seen);
        }

        fclose($handle);
        $this->imports->updateCounts($importId, $total, $imported);
        $this->audit->record("import", $importId, "created", "Import created with {$imported} leads.");

        Response::redirect("/projects/show?id=" . $projectId);
    }

    public function edit(Request $request): void
    {
        $id = (int) ($request->query["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $import = $this->imports->find($id);
        if ($import === null) {
            Response::notFound();
            return;
        }

        $project = $this->projects->find((int) ($import["project_id"] ?? 0));
        if ($project === null) {
            Response::redirect("/projects");
            return;
        }

        View::render("imports/edit", [
            "project" => $project,
            "import" => $import,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $import = $this->imports->find($id);
        if ($import === null) {
            Response::redirect("/projects");
            return;
        }

        $name = trim((string) ($request->body["name"] ?? ""));
        if ($name === "") {
            Response::redirect("/imports/edit?id=" . $id);
            return;
        }

        $this->imports->update($id, ["name" => $name]);
        $this->audit->record("import", $id, "updated", "Import updated.");
        Response::redirect("/projects/show?id=" . (int) ($import["project_id"] ?? 0));
    }

    public function delete(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $import = $this->imports->find($id);
        if ($import === null) {
            Response::redirect("/projects");
            return;
        }

        $projectId = (int) ($import["project_id"] ?? 0);
        $this->leads->deleteByImport($id);
        $this->imports->delete($id);
        $this->audit->record("import", $id, "deleted", "Import deleted with cascade.");
        Response::redirect("/projects/show?id=" . $projectId);
    }

    public function archive(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $import = $this->imports->find($id);
        if ($import === null) {
            Response::redirect("/projects");
            return;
        }

        $projectId = (int) ($import["project_id"] ?? 0);
        $this->imports->archive($id);
        $this->leads->archiveByImport($id);
        $this->audit->record("import", $id, "archived", "Import archived with cascade.");
        Response::redirect("/projects/show?id=" . $projectId);
    }

    public function restore(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $import = $this->imports->find($id);
        if ($import === null) {
            Response::redirect("/projects");
            return;
        }

        $projectId = (int) ($import["project_id"] ?? 0);
        $this->imports->restore($id);
        $this->leads->restoreByImport($id);
        $this->audit->record("import", $id, "restored", "Import restored with cascade.");
        Response::redirect("/projects/show?id=" . $projectId);
    }

    private function processRow(array $row, int $projectId, int $importId, array $project, int &$imported, int &$total, array &$seen): void
    {
        $total++;

        $name = $this->column($row, 4);
        if ($name === "") {
            $name = $this->column($row, 5);
        }

        $rating = $this->toFloat($this->column($row, 17));
        if ($rating !== null && ($rating < 0 || $rating > 5)) {
            $rating = null;
        }

        $payload = [
            "project_id" => $projectId,
            "import_id" => $importId,
            "name" => $this->truncate($name !== "" ? $name : "Lead " . $total, 180),
            "address" => $this->truncate($this->column($row, 5), 255),
            "phone" => $this->truncate($this->column($row, 8), 40),
            "mobile" => $this->truncate($this->column($row, 10), 40),
            "category" => $this->truncate(
                $this->column($row, 14) !== "" ? $this->column($row, 14) : ($project["niche"] ?? ""),
                120
            ),
            "comments" => $this->column($row, 16),
            "rating" => $rating,
            "website" => $this->truncate($this->column($row, 18), 255),
            "google_maps_url" => $this->truncate($this->column($row, 1), 255),
            "status" => "new",
            "score" => 0,
            "imported_at" => date("Y-m-d H:i:s"),
        ];

        $dedupeKey = $this->leads->dedupeKeyFromData($payload);
        if ($dedupeKey !== null) {
            if (isset($seen[$dedupeKey])) {
                return;
            }
            $payload["dedupe_key"] = $dedupeKey;
            $seen[$dedupeKey] = true;
        }

        $createdId = $this->leads->createIfNotExists($payload);
        if ($createdId > 0) {
            $imported++;
        }
    }

    private function column(array $row, int $index): string
    {
        return isset($row[$index]) ? trim((string) $row[$index]) : "";
    }

    private function toFloat(string $value): ?float
    {
        if ($value === "") {
            return null;
        }

        $normalized = str_replace(",", ".", $value);
        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function truncate(string $value, int $limit): string
    {
        if (strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit);
    }

    private function isHeaderRow(array $row): bool
    {
        $joined = strtolower(implode(",", $row));
        return str_contains($joined, "listing_url") || str_contains($joined, "fulladdr") || str_contains($joined, "name");
    }
}
