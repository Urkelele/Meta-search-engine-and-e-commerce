<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/../../includes/db.php";
$conn = db();

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($email === "" || $password === "") {
  echo json_encode(["success" => false, "error" => "Missing credentials"]);
  exit;
}

// Buscar usuario
$stmt = $conn->prepare("
  SELECT id, email, password_hash, is_verified
  FROM mse_users
  WHERE email = ?
  LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
  echo json_encode(["success" => false, "error" => "Invalid email or password"]);
  exit;
}

// Verificar password
if (!password_verify($password, $user["password_hash"])) {
  echo json_encode(["success" => false, "error" => "Invalid email or password"]);
  exit;
}

// 🔐 COMPROBACIÓN CLAVE
if ((int)$user["is_verified"] !== 1) {
  echo json_encode([
    "success" => false,
    "error" => "Please verify your email before logging in"
  ]);
  exit;
}

// ✅ Login OK
$_SESSION["user"] = [
  "id"    => $user["id"],
  "email" => $user["email"]
];

echo json_encode(["success" => true]);