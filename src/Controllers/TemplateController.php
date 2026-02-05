<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Lead;
use App\Models\Template;

final class TemplateController
{
    private Template $templates;
    private Lead $leads;

    public function __construct()
    {
        $this->templates = new Template();
        $this->leads = new Lead();
    }

    public function index(Request $request): void
    {
        if (($request->query["seed"] ?? "") === "1") {
            $this->seedDefaults();
            Response::redirect("/templates/whatsapp");
            return;
        }

        $templates = $this->templates->allByType("whatsapp");
        $leads = $this->leads->search([
            "order_by" => "created_at",
            "direction" => "DESC",
        ]);

        View::render("templates/whatsapp", [
            "templates" => $templates,
            "leads" => $leads,
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim((string) ($request->body["name"] ?? ""));
        $content = trim((string) ($request->body["content"] ?? ""));
        if ($name === "" || $content === "") {
            Response::redirect("/templates/whatsapp");
            return;
        }

        $this->templates->create([
            "type" => "whatsapp",
            "name" => $name,
            "content" => $content,
            "variables" => json_encode(["{nome}", "{cidade}", "{categoria}"], JSON_UNESCAPED_UNICODE),
            "active" => 1,
        ]);

        Response::redirect("/templates/whatsapp");
    }

    public function send(Request $request): void
    {
        $templateId = (int) ($request->body["template_id"] ?? 0);
        $phone = trim((string) ($request->body["phone"] ?? ""));
        $leadId = (int) ($request->body["lead_id"] ?? 0);

        $template = $this->templates->find($templateId);
        if ($template === null) {
            Response::redirect("/templates/whatsapp");
            return;
        }

        $lead = $leadId > 0 ? $this->leads->find($leadId) : null;
        $message = (string) ($template["content"] ?? "");
        if ($lead !== null) {
            $message = str_replace(
                ["{nome}", "{cidade}", "{categoria}"],
                [
                    (string) ($lead["name"] ?? ""),
                    (string) ($lead["city"] ?? ""),
                    (string) ($lead["category"] ?? ""),
                ],
                $message
            );
            if ($phone === "") {
                $phone = (string) ($lead["mobile"] ?? $lead["phone"] ?? "");
            }
        }

        $phone = $this->normalizeWhatsappPhone($phone);
        if ($phone === "") {
            Response::redirect("/templates/whatsapp");
            return;
        }

        $encoded = rawurlencode($message);
        $link = "https://wa.me/" . $phone . "?text=" . $encoded;
        Response::redirect($link);
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

    private function seedDefaults(): void
    {
        $defaults = [
            [
                "name" => "Landing Page",
                "content" => "Oi {nome}, tudo bem? Vi o seu negócio em {cidade} e notei oportunidades para captar mais clientes com uma landing page focada em conversão. Posso te mostrar um exemplo rápido?",
            ],
            [
                "name" => "Site Institucional",
                "content" => "Olá {nome}, tudo certo? Analisei sua presença online e acredito que um site institucional moderno pode aumentar sua credibilidade e trazer mais contatos. Posso te enviar uma proposta simples?",
            ],
            [
                "name" => "Design Gráfico",
                "content" => "Oi {nome}! Percebi que sua comunicação visual pode ganhar mais consistência. Posso te apresentar um pacote de design gráfico para fortalecer a marca?",
            ],
            [
                "name" => "Social Media",
                "content" => "Olá {nome}, tudo bem? Vi seu perfil e acredito que uma rotina estratégica de social media pode gerar mais demanda. Posso te mostrar um plano inicial?",
            ],
        ];

        foreach ($defaults as $item) {
            $this->templates->create([
                "type" => "whatsapp",
                "name" => $item["name"],
                "content" => $item["content"],
                "variables" => json_encode(["{nome}", "{cidade}", "{categoria}"], JSON_UNESCAPED_UNICODE),
                "active" => 1,
            ]);
        }
    }
}
