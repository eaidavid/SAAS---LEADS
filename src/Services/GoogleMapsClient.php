<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Env;

final class GoogleMapsClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = (string) Config::get("maps.base_url", "");
        $this->timeout = (int) Config::get("maps.timeout", 12);
    }

    public function textSearch(array $params): array
    {
        $key = Env::get("GOOGLE_MAPS_API_KEY", "");
        if ($key === "") {
            return [
                "error" => "Missing GOOGLE_MAPS_API_KEY in .env",
                "results" => [],
            ];
        }

        $queryParams = array_filter([
            "query" => $params["query"] ?? null,
            "location" => $params["location"] ?? null,
            "radius" => $params["radius"] ?? null,
            "type" => $params["type"] ?? null,
            "key" => $key,
        ], fn ($value) => $value !== null && $value !== "");

        $url = rtrim($this->baseUrl, "/") . "/textsearch/json?" . http_build_query($queryParams);
        $response = $this->get($url);
        if ($response["error"] !== null) {
            return [
                "error" => $response["error"],
                "results" => [],
            ];
        }

        $payload = json_decode($response["body"], true);
        if (!is_array($payload)) {
            return [
                "error" => "Invalid response from Google Maps API.",
                "results" => [],
            ];
        }

        if (($payload["status"] ?? "") !== "OK") {
            $message = $payload["error_message"] ?? $payload["status"] ?? "Google Maps request failed.";
            return [
                "error" => $message,
                "results" => [],
            ];
        }

        return [
            "error" => null,
            "results" => $payload["results"] ?? [],
        ];
    }

    private function get(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        $body = curl_exec($ch);
        $error = null;
        if ($body === false) {
            $error = curl_error($ch);
        }
        curl_close($ch);

        return [
            "body" => $body === false ? "" : $body,
            "error" => $error,
        ];
    }
}
