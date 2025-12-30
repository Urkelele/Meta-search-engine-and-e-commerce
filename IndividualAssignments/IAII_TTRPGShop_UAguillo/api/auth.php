<?php
header("Content-Type: application/json; charset=utf-8");

define("API_KEY", "ttrpg-secret-123");

function require_api_key(): void {
    $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($key === '') {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "API key missing"]);
        exit;
    }
    if ($key !== API_KEY) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Invalid API key"]);
        exit;
    }
}
