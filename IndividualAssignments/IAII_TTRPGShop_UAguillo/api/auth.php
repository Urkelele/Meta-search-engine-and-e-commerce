<?php
// api/auth.php

// API KEY compartida entre el MSE y este IA
define("API_KEY", "ttrpg-secret-123");

// Comprobamos que la cabecera exista
if (!isset($_SERVER['HTTP_X_API_KEY'])) {
    http_response_code(401);
    echo json_encode(["error" => "API key missing"]);
    exit;
}

// Comprobamos que sea correcta
if ($_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid API key"]);
    exit;
}

// Si llega aquí, la API está autenticada
