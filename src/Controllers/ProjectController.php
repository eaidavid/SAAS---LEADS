<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\ImportBatch;
use App\Models\Lead;
use App\Models\Project;

final class ProjectController
{
    private Project $projects;
    private ImportBatch $imports;
    private Lead $leads;
    private AuditLog $audit;

    public function __construct()
    {
        $this->projects = new Project();
        $this->imports = new ImportBatch();
        $this->leads = new Lead();
        $this->audit = new AuditLog();
    }

    public function index(Request $request): void
    {
        $projects = $this->projects->listWithCounts();
        View::render("projects/index", [
            "projects" => $projects,
        ]);
    }

    public function create(Request $request): void
    {
        View::render("projects/create", [
            "niches" => Config::get("niches", []),
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim((string) ($request->body["name"] ?? ""));
        if ($name === "") {
            Response::redirect("/projects/new");
            return;
        }

        $niche = trim((string) ($request->body["niche"] ?? ""));
        if ($niche === "") {
            $niche = trim((string) ($request->body["niche_custom"] ?? ""));
        }

        $id = $this->projects->create([
            "name" => $name,
            "niche" => $niche !== "" ? $niche : null,
            "description" => trim((string) ($request->body["description"] ?? "")) ?: null,
        ]);

        Response::redirect("/projects/show?id=" . $id);
    }

    public function show(Request $request): void
    {
        $id = (int) ($request->query["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $project = $this->projects->find($id);
        if ($project === null) {
            Response::notFound();
            return;
        }

        $imports = $this->imports->byProject($id);
        $archivedImports = $this->imports->archivedByProject($id);

        View::render("projects/show", [
            "project" => $project,
            "imports" => $imports,
            "archivedImports" => $archivedImports,
        ]);
    }

    public function edit(Request $request): void
    {
        $id = (int) ($request->query["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $project = $this->projects->find($id);
        if ($project === null) {
            Response::notFound();
            return;
        }

        View::render("projects/edit", [
            "project" => $project,
            "niches" => Config::get("niches", []),
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/projects");
            return;
        }

        $name = trim((string) ($request->body["name"] ?? ""));
        if ($name === "") {
            Response::redirect("/projects/edit?id=" . $id);
            return;
        }

        $niche = trim((string) ($request->body["niche"] ?? ""));
        if ($niche === "") {
            $niche = trim((string) ($request->body["niche_custom"] ?? ""));
        }

        $this->projects->update($id, [
            "name" => $name,
            "niche" => $niche !== "" ? $niche : null,
            "description" => trim((string) ($request->body["description"] ?? "")) ?: null,
        ]);

        $this->audit->record("project", $id, "updated", "Project updated.");
        Response::redirect("/projects/show?id=" . $id);
    }

    public function delete(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id > 0) {
            $this->leads->deleteByProject($id);
            $this->imports->deleteByProject($id);
            $this->projects->delete($id);
            $this->audit->record("project", $id, "deleted", "Project deleted with cascade.");
        }

        Response::redirect("/projects");
    }

    public function archive(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id > 0) {
            $this->projects->archive($id);
            $this->imports->restoreByProject($id);
            $this->leads->archiveByProject($id);
            $this->audit->record("project", $id, "archived", "Project archived with cascade.");
        }

        Response::redirect("/projects");
    }

    public function archived(Request $request): void
    {
        $projects = $this->projects->listArchived();
        View::render("projects/archived", [
            "projects" => $projects,
        ]);
    }

    public function restore(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id > 0) {
            $this->projects->restore($id);
            $this->imports->archiveByProject($id);
            $this->leads->restoreByProject($id);
            $this->audit->record("project", $id, "restored", "Project restored with cascade.");
        }

        Response::redirect("/projects/archived");
    }
}
