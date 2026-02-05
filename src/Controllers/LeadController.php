<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Interaction;
use App\Models\Lead;
use App\Models\Template;

final class LeadController
{
    private Lead $leads;
    private Interaction $interactions;
    private Template $templates;

    public function __construct()
    {
        $this->leads = new Lead();
        $this->interactions = new Interaction();
        $this->templates = new Template();
    }

    public function index(Request $request): void
    {
        $statuses = Config::get("leads.statuses", []);
        $filters = [
            "status" => $request->query["status"] ?? "",
            "q" => $request->query["q"] ?? "",
            "category" => $request->query["category"] ?? "",
            "min_rating" => $request->query["min_rating"] ?? "",
            "order_by" => $request->query["order_by"] ?? "created_at",
            "direction" => $request->query["direction"] ?? "DESC",
            "project_id" => $request->query["project_id"] ?? null,
            "import_id" => $request->query["import_id"] ?? null,
        ];

        $leads = $this->leads->search($filters);
        foreach ($leads as &$lead) {
            $phone = (string) ($lead["mobile"] ?? $lead["phone"] ?? "");
            $lead["whatsapp_phone"] = $this->normalizeWhatsappPhone($phone);
        }
        unset($lead);
        $templates = $this->templates->allByType("whatsapp");
        $counts = $this->leads->countByStatus($filters);
        $projects = (new \App\Models\Project())->all();
        $imports = [];
        if (!empty($filters["project_id"]) && is_numeric($filters["project_id"])) {
            $imports = (new \App\Models\ImportBatch())->byProject((int) $filters["project_id"]);
        }

        View::render("leads/index", [
            "leads" => $leads,
            "statuses" => $statuses,
            "counts" => $counts,
            "filters" => $filters,
            "projects" => $projects,
            "imports" => $imports,
            "templates" => $templates,
        ]);
    }

    public function create(Request $request): void
    {
        $statuses = Config::get("leads.statuses", []);
        View::render("leads/create", [
            "statuses" => $statuses,
            "lead" => $this->emptyLead(),
        ]);
    }

    public function store(Request $request): void
    {
        $statuses = Config::get("leads.statuses", []);
        $payload = $this->leadPayload($request->body, $statuses);
        if ($payload["name"] === "") {
            Response::redirect("/leads/new");
            return;
        }

        $id = $this->leads->createIfNotExists($payload);
        if ($id <= 0) {
            Response::redirect("/leads?duplicate=1");
            return;
        }

        Response::redirect("/leads/show?id=" . $id);
    }

    public function show(Request $request): void
    {
        $id = (int) ($request->query["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/leads");
            return;
        }

        $lead = $this->leads->find($id);
        if ($lead === null) {
            Response::notFound();
            return;
        }

        $lead["tags"] = $this->tagsToString($lead["tags"] ?? null);
        $lead["whatsapp_phone"] = $this->normalizeWhatsappPhone((string) ($lead["mobile"] ?? $lead["phone"] ?? ""));
        $statuses = Config::get("leads.statuses", []);
        $interactions = $this->interactions->byLead($id);
        $types = Config::get("leads.interaction_types", []);
        $templates = $this->templates->allByType("whatsapp");

        View::render("leads/show", [
            "lead" => $lead,
            "statuses" => $statuses,
            "interactions" => $interactions,
            "types" => $types,
            "templates" => $templates,
        ]);
    }

    public function edit(Request $request): void
    {
        $id = (int) ($request->query["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/leads");
            return;
        }

        $lead = $this->leads->find($id);
        if ($lead === null) {
            Response::notFound();
            return;
        }

        $lead["tags"] = $this->tagsToString($lead["tags"] ?? null);
        $statuses = Config::get("leads.statuses", []);

        View::render("leads/edit", [
            "lead" => $lead,
            "statuses" => $statuses,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/leads");
            return;
        }

        $existing = $this->leads->find($id);
        if ($existing === null) {
            Response::redirect("/leads");
            return;
        }

        $statuses = Config::get("leads.statuses", []);
        $payload = $this->leadPayload($request->body, $statuses);
        if ($payload["name"] === "") {
            Response::redirect("/leads/edit?id=" . $id);
            return;
        }

        $this->leads->update($id, $payload);

        if (($existing["status"] ?? "") !== $payload["status"]) {
            $from = $statuses[$existing["status"] ?? ""] ?? ($existing["status"] ?? "");
            $to = $statuses[$payload["status"]] ?? $payload["status"];
            $this->interactions->create([
                "lead_id" => $id,
                "user_id" => 0,
                "type" => "status_change",
                "message" => "Status changed from {$from} to {$to}.",
                "date" => date("Y-m-d H:i:s"),
            ]);
        }

        Response::redirect("/leads/show?id=" . $id);
    }

    public function updateStatus(Request $request): void
    {
        $id = (int) ($request->body["lead_id"] ?? 0);
        $status = (string) ($request->body["status"] ?? "");
        $returnUrl = (string) ($request->body["return_url"] ?? "/leads");
        if ($returnUrl === "" || !str_starts_with($returnUrl, "/")) {
            $returnUrl = "/leads";
        }

        if ($id <= 0) {
            Response::redirect($returnUrl);
            return;
        }

        $lead = $this->leads->find($id);
        if ($lead === null) {
            Response::redirect($returnUrl);
            return;
        }

        $statuses = Config::get("leads.statuses", []);
        if (!array_key_exists($status, $statuses)) {
            Response::redirect($returnUrl);
            return;
        }

        $current = (string) ($lead["status"] ?? "");
        if ($current !== $status) {
            $this->leads->update($id, ["status" => $status]);
            $from = $statuses[$current] ?? $current;
            $to = $statuses[$status] ?? $status;
            $this->interactions->create([
                "lead_id" => $id,
                "user_id" => 0,
                "type" => "status_change",
                "message" => "Status changed from {$from} to {$to}.",
                "date" => date("Y-m-d H:i:s"),
            ]);
        }

        Response::redirect($returnUrl);
    }

    public function updateNotes(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id <= 0) {
            Response::redirect("/leads");
            return;
        }

        $tags = $this->normalizeTags((string) ($request->body["tags"] ?? ""));
        $tagsJson = $tags === [] ? null : json_encode($tags, JSON_UNESCAPED_UNICODE);
        $notes = $this->nullIfEmpty($request->body["notes"] ?? null);

        $this->leads->update($id, [
            "tags" => $tagsJson,
            "notes" => $notes,
        ]);

        Response::redirect("/leads/show?id=" . $id);
    }

    public function delete(Request $request): void
    {
        $id = (int) ($request->body["id"] ?? 0);
        if ($id > 0) {
            $this->leads->delete($id);
        }

        Response::redirect("/leads");
    }

    public function addInteraction(Request $request): void
    {
        $leadId = (int) ($request->body["lead_id"] ?? 0);
        if ($leadId <= 0) {
            Response::redirect("/leads");
            return;
        }

        $message = trim((string) ($request->body["message"] ?? ""));
        if ($message === "") {
            Response::redirect("/leads/show?id=" . $leadId);
            return;
        }

        $types = Config::get("leads.interaction_types", []);
        $type = (string) ($request->body["type"] ?? "note");
        if (!array_key_exists($type, $types)) {
            $type = "note";
        }

        $dateRaw = trim((string) ($request->body["date"] ?? ""));
        $date = $dateRaw === "" ? null : $dateRaw;

        $this->interactions->create([
            "lead_id" => $leadId,
            "user_id" => 0,
            "type" => $type,
            "message" => $message,
            "date" => $date,
        ]);

        Response::redirect("/leads/show?id=" . $leadId);
    }

    public function sendWhatsapp(Request $request): void
    {
        $leadId = (int) ($request->body["lead_id"] ?? 0);
        if ($leadId <= 0) {
            Response::redirect("/leads");
            return;
        }

        $lead = $this->leads->find($leadId);
        if ($lead === null) {
            Response::redirect("/leads");
            return;
        }

        $message = trim((string) ($request->body["message"] ?? ""));
        $templateId = (int) ($request->body["template_id"] ?? 0);
        if ($message === "" && $templateId > 0) {
            $template = $this->templates->find($templateId);
            if ($template !== null) {
                $message = (string) ($template["content"] ?? "");
            }
        }

        if ($message === "") {
            Response::redirect("/leads/show?id=" . $leadId);
            return;
        }

        $message = $this->applyMessageVariables($message, $lead);

        $phone = trim((string) ($request->body["phone"] ?? ""));
        if ($phone === "") {
            $phone = (string) ($lead["mobile"] ?? $lead["phone"] ?? "");
        }

        $phone = $this->normalizeWhatsappPhone($phone);
        if ($phone === "") {
            Response::redirect("/leads/show?id=" . $leadId);
            return;
        }

        $encoded = rawurlencode($message);
        $link = "https://wa.me/" . $phone . "?text=" . $encoded;
        Response::redirect($link);
    }

    public function export(Request $request): void
    {
        $filters = [
            "status" => $request->query["status"] ?? "",
            "q" => $request->query["q"] ?? "",
        ];
        $leads = $this->leads->search($filters);

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=leads.csv");

        $output = fopen("php://output", "w");
        if ($output === false) {
            return;
        }

        $headers = [
            "id",
            "project_id",
            "import_id",
            "google_maps_url",
            "name",
            "phone",
            "mobile",
            "email",
            "website",
            "address",
            "city",
            "state",
            "category",
            "comments",
            "rating",
            "reviews_count",
            "status",
            "score",
            "tags",
            "notes",
            "imported_at",
            "created_at",
        ];
        fputcsv($output, $headers);

        foreach ($leads as $lead) {
            $lead["tags"] = $this->tagsToString($lead["tags"] ?? null);
            $row = [];
            foreach ($headers as $column) {
                $row[] = $lead[$column] ?? "";
            }
            fputcsv($output, $row);
        }

        fclose($output);
    }

    public function import(Request $request): void
    {
        if (!isset($_FILES["csv"]) || $_FILES["csv"]["error"] !== UPLOAD_ERR_OK) {
            Response::redirect("/leads");
            return;
        }

        $handle = fopen($_FILES["csv"]["tmp_name"], "r");
        if ($handle === false) {
            Response::redirect("/leads");
            return;
        }

        $headers = fgetcsv($handle);
        if (!is_array($headers)) {
            fclose($handle);
            Response::redirect("/leads");
            return;
        }

        $map = [];
        foreach ($headers as $index => $header) {
            $map[strtolower(trim($header))] = $index;
        }

        $statuses = Config::get("leads.statuses", []);
        while (($row = fgetcsv($handle)) !== false) {
            $name = $this->rowValue($row, $map, "name");
            if ($name === "") {
                continue;
            }

            $payload = $this->leadPayload([
                "name" => $name,
                "phone" => $this->rowValue($row, $map, "phone"),
                "email" => $this->rowValue($row, $map, "email"),
                "website" => $this->rowValue($row, $map, "website"),
                "address" => $this->rowValue($row, $map, "address"),
                "city" => $this->rowValue($row, $map, "city"),
                "state" => $this->rowValue($row, $map, "state"),
                "category" => $this->rowValue($row, $map, "category"),
                "rating" => $this->rowValue($row, $map, "rating"),
                "reviews_count" => $this->rowValue($row, $map, "reviews_count"),
                "status" => $this->rowValue($row, $map, "status"),
                "score" => $this->rowValue($row, $map, "score"),
                "tags" => $this->rowValue($row, $map, "tags"),
                "notes" => $this->rowValue($row, $map, "notes"),
            ], $statuses);

            if ($payload["name"] !== "") {
                $this->leads->createIfNotExists($payload);
            }
        }

        fclose($handle);
        Response::redirect("/leads");
    }

    private function leadPayload(array $input, array $statuses): array
    {
        $name = trim((string) ($input["name"] ?? ""));
        $status = (string) ($input["status"] ?? "new");
        if (!array_key_exists($status, $statuses)) {
            $status = "new";
        }

        $tags = $this->normalizeTags((string) ($input["tags"] ?? ""));
        $tagsJson = $tags === [] ? null : json_encode($tags, JSON_UNESCAPED_UNICODE);

        return [
            "name" => $name,
            "phone" => $this->nullIfEmpty($input["phone"] ?? null),
            "mobile" => $this->nullIfEmpty($input["mobile"] ?? null),
            "email" => $this->nullIfEmpty($input["email"] ?? null),
            "website" => $this->nullIfEmpty($input["website"] ?? null),
            "address" => $this->nullIfEmpty($input["address"] ?? null),
            "city" => $this->nullIfEmpty($input["city"] ?? null),
            "state" => $this->nullIfEmpty($input["state"] ?? null),
            "category" => $this->nullIfEmpty($input["category"] ?? null),
            "rating" => $this->floatOrNull($input["rating"] ?? null),
            "reviews_count" => $this->intOrNull($input["reviews_count"] ?? null),
            "status" => $status,
            "score" => $this->intOrNull($input["score"] ?? null) ?? 0,
            "tags" => $tagsJson,
            "notes" => $this->nullIfEmpty($input["notes"] ?? null),
        ];
    }

    private function normalizeTags(string $raw): array
    {
        $parts = array_map("trim", explode(",", $raw));
        $parts = array_filter($parts, fn ($value) => $value !== "");
        return array_values(array_unique($parts));
    }

    private function tagsToString(?string $tags): string
    {
        if ($tags === null || $tags === "") {
            return "";
        }

        $decoded = json_decode($tags, true);
        if (!is_array($decoded)) {
            return $tags;
        }

        return implode(", ", $decoded);
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ""));
        return $value === "" ? null : $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === "") {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === "") {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function emptyLead(): array
    {
        return [
            "id" => 0,
            "name" => "",
            "phone" => "",
            "mobile" => "",
            "email" => "",
            "website" => "",
            "address" => "",
            "city" => "",
            "state" => "",
            "category" => "",
            "rating" => "",
            "reviews_count" => "",
            "status" => "new",
            "score" => 0,
            "tags" => "",
            "notes" => "",
        ];
    }

    private function applyMessageVariables(string $message, array $lead): string
    {
        return str_replace(
            ["{nome}", "{cidade}", "{categoria}"],
            [
                (string) ($lead["name"] ?? ""),
                (string) ($lead["city"] ?? ""),
                (string) ($lead["category"] ?? ""),
            ],
            $message
        );
    }

    private function normalizeWhatsappPhone(string $phone): string
    {
        $digits = preg_replace("/\\D+/", "", $phone);
        if ($digits === "" || $digits === null) {
            return "";
        }

        if (str_starts_with($digits, "55")) {
            if (strlen($digits) >= 13 && $digits[2] === "0") {
                $digits = "55" . substr($digits, 3);
            }
            if (strlen($digits) > 13) {
                return "55" . substr($digits, -11);
            }

            return $digits;
        }

        if (strlen($digits) > 11) {
            $digits = substr($digits, -11);
        }

        $digits = ltrim($digits, "0");
        if ($digits === "") {
            return "";
        }

        return "55" . $digits;
    }

    private function rowValue(array $row, array $map, string $key): string
    {
        if (!array_key_exists($key, $map)) {
            return "";
        }

        $index = $map[$key];
        return isset($row[$index]) ? trim((string) $row[$index]) : "";
    }
}
