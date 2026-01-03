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

// Fetch user
$stmt = $conn->prepare("SELECT id, email, password_hash FROM mse_users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($pass, $user["password_hash"])) {
  http_response_code(401);
  echo json_encode(["success"=>false, "error"=>"Invalid credentials"]);
  exit;
}

login_user((int)$user["id"], $user["email"]);

echo json_encode(["success"=>true, "user"=>["id"=>(int)$user["id"], "email"=>$user["email"]]]);
