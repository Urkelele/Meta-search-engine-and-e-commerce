<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function login_user(int $id, string $email): void {
  session_regenerate_id(true);
  $_SESSION['user'] = [
    "id" => $id,
    "email" => $email
  ];
}

function logout_user(): void {
  if (session_status() === PHP_SESSION_NONE) session_start();
  $_SESSION = [];
  session_destroy();
}

function require_login_api(): void {
  if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(["success"=>false, "error"=>"Not logged in"]);
    exit;
  }
}
