<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/../../includes/DB.php"; // <-- ajusta si cambia
$conn = db();

$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input["email"] ?? "");
$pass  = (string)($input["password"] ?? "");

if ($email === "" || $pass === "") {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Missing email or password"]);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Invalid email"]);
  exit;
}

if (strlen($pass) < 6) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Password must be at least 6 characters"]);
  exit;
}

// 1) no permitir duplicados
$chk = $conn->prepare("SELECT id, is_verified FROM mse_users WHERE email = ? LIMIT 1");
$chk->bind_param("s", $email);
$chk->execute();
$existing = $chk->get_result()->fetch_assoc();
$chk->close();

if ($existing) {
  http_response_code(409);
  echo json_encode(["success" => false, "error" => "Email already registered"]);
  exit;
}

// 2) crear usuario (is_verified=0)
$hash = password_hash($pass, PASSWORD_DEFAULT);
$createdAt = date("Y-m-d");

$ins = $conn->prepare("INSERT INTO mse_users (email, password_hash, is_verified, created_at) VALUES (?, ?, 0, ?)");
$ins->bind_param("sss", $email, $hash, $createdAt);

if (!$ins->execute()) {
  $ins->close();
  http_response_code(500);
  echo json_encode(["success" => false, "error" => "Could not create user"]);
  exit;
}
$userId = $ins->insert_id;
$ins->close();

// 3) token verify
$token = bin2hex(random_bytes(32)); // 64 chars
$expire = date("Y-m-d H:i:s", time() + 24*60*60); // 24h

$tok = $conn->prepare("INSERT INTO mse_tokens (user_id, token, type, expire_time) VALUES (?, ?, 'email_verify', ?)");
$tok->bind_param("iss", $userId, $token, $expire);
$tok->execute();
$tok->close();

// 4) enviar email (PHPMailer)
require __DIR__ . "/../../includes/mailer.php"; // lo creamos abajo

$sent = sendVerificationEmailMSE($email, $token);

if (!$sent) {
  // Si falla el mail, puedes decidir borrar usuario+token o dejarlo.
  // Aquí lo dejo creado pero aviso.
  echo json_encode([
    "success" => true,
    "warning" => "User created, but email could not be sent. Check SMTP config."
  ]);
  exit;
}

echo json_encode([
  "success" => true,
  "message" => "Account created. Check your email to verify your account."
]);
