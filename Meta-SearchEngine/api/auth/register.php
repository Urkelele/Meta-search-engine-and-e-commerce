<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../../includes/db.php";
require_once __DIR__ . "/../../includes/session.php";

$conn = db();
$data = json_decode(file_get_contents("php://input"), true) ?: [];

$email = trim($data["email"] ?? "");
$pass  = (string)($data["password"] ?? "");

if ($email === "" || $pass === "") {
  http_response_code(400);
  echo json_encode(["success"=>false, "error"=>"Missing fields"]);
  exit;
}

// email exists?
$stmt = $conn->prepare("SELECT id FROM mse_users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
  http_response_code(409);
  echo json_encode(["success"=>false, "error"=>"Email already exists"]);
  exit;
}

// create user
$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO mse_users (email, password_hash, created_at) VALUES (?, ?, CURDATE())");
$stmt->bind_param("ss", $email, $hash);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(["success"=>false, "error"=>$stmt->error]);
  exit;
}

$userId = $conn->insert_id;
$stmt->close();

// auto login
login_user($userId, $email);

echo json_encode(["success"=>true, "user"=>["id"=>$userId, "email"=>$email]]);
