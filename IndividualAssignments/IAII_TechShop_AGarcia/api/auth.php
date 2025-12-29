<?php
// api/_auth.php
header("Content-Type: application/json; charset=utf-8");

const API_KEY = "TECHSHOP_SECRET_123"; // pon la misma que en ia_config.php del MSE

function require_api_key(): void {
    $headers = getallheaders();
    $key = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? null;

    if (!$key || $key !== API_KEY) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
}
