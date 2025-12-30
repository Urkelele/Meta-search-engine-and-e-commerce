<?php
// // api/auth.php
// header("Content-Type: application/json; charset=utf-8");

// const API_KEY = "TECHSHOP_SECRET_123"; // pon la misma que en ia_config.php del MSE

// function require_api_key(): void {
//     $headers = getallheaders();
//     $key = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? null;

//     if (!$key || $key !== API_KEY) {
//         http_response_code(401);
//         echo json_encode(["error" => "Unauthorized"]);
//         exit;
//     }
// }


// api/auth.php

const API_KEY = "TECHSHOP_SECRET_123";

function require_api_key(): void {
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    // fallback si getallheaders() no está
    if (!$headers) {
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name = str_replace('_', '-', substr($k, 5));
                $headers[$name] = $v;
            }
        }
    }

    $key = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? null;

    if (!$key || $key !== API_KEY) {
        http_response_code(401);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["success"=>false, "error"=>"Unauthorized"]);
        exit;
    }
}

