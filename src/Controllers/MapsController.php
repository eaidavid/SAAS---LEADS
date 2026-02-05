<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Lead;
use App\Services\GoogleMapsClient;

final class MapsController
{
    private GoogleMapsClient $client;
    private Lead $leads;

    public function __construct()
    {
        $this->client = new GoogleMapsClient();
        $this->leads = new Lead();
    }

    public function form(Request $request): void
    {
        View::render("leads/search", [
            "results" => [],
            "error" => null,
            "filters" => [
                "keyword" => "",
                "location" => "",
                "radius" => "5000",
                "type" => "",
            ],
        ]);
    }

    public function search(Request $request): void
    {
        $keyword = trim((string) ($request->body["keyword"] ?? ""));
        $location = trim((string) ($request->body["location"] ?? ""));
        $radius = trim((string) ($request->body["radius"] ?? "5000"));
        $type = trim((string) ($request->body["type"] ?? ""));

        if ($keyword === "" || $location === "") {
            View::render("leads/search", [
                "results" => [],
                "error" => "Keyword and location are required.",
                "filters" => [
                    "keyword" => $keyword,
                    "location" => $location,
                    "radius" => $radius,
                    "type" => $type,
                ],
            ]);
            return;
        }

        $query = $keyword . " in " . $location;
        $response = $this->client->textSearch([
            "query" => $query,
            "radius" => $radius !== "" ? $radius : null,
            "type" => $type !== "" ? $type : null,
        ]);

        $results = $this->normalizeResults($response["results"] ?? []);
        $_SESSION["maps_results"] = $results;

        View::render("leads/search", [
            "results" => $results,
            "error" => $response["error"] ?? null,
            "filters" => [
                "keyword" => $keyword,
                "location" => $location,
                "radius" => $radius,
                "type" => $type,
            ],
        ]);
    }

    public function save(Request $request): void
    {
        $selected = $request->body["selected"] ?? [];
        if (!is_array($selected) || $selected === []) {
            Response::redirect("/leads/search");
            return;
        }

        $results = $_SESSION["maps_results"] ?? [];
        $saved = 0;
        foreach ($results as $result) {
            if (!in_array($result["place_id"], $selected, true)) {
                continue;
            }

            $payload = [
                "place_id" => $result["place_id"],
                "name" => $result["name"],
                "address" => $result["address"],
                "city" => $result["city"],
                "state" => $result["state"],
                "category" => $result["category"],
                "google_maps_url" => $result["google_maps_url"],
                "rating" => $result["rating"],
                "reviews_count" => $result["reviews_count"],
                "latitude" => $result["latitude"],
                "longitude" => $result["longitude"],
                "status" => "new",
                "score" => 0,
            ];

            $createdId = $this->leads->createIfNotExists($payload);
            if ($createdId > 0) {
                $saved++;
            }
        }

        Response::redirect("/leads?imported=" . $saved);
    }

    private function normalizeResults(array $results): array
    {
        $normalized = [];
        foreach ($results as $item) {
            $placeId = (string) ($item["place_id"] ?? "");
            if ($placeId === "") {
                continue;
            }

            $address = (string) ($item["formatted_address"] ?? "");
            $parts = array_map("trim", explode(",", $address));
            $city = $parts[count($parts) - 2] ?? "";
            $state = $parts[count($parts) - 1] ?? "";

            $normalized[] = [
                "place_id" => $placeId,
                "name" => (string) ($item["name"] ?? ""),
                "address" => $address,
                "city" => $city,
                "state" => $state,
                "category" => (string) (($item["types"][0] ?? "") ?? ""),
                "google_maps_url" => "https://maps.google.com/?q=place_id:" . $placeId,
                "rating" => $item["rating"] ?? null,
                "reviews_count" => $item["user_ratings_total"] ?? null,
                "latitude" => $item["geometry"]["location"]["lat"] ?? null,
                "longitude" => $item["geometry"]["location"]["lng"] ?? null,
            ];
        }

        return $normalized;
    }
}
