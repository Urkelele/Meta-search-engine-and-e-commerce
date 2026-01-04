<?php
require __DIR__ . "/../includes/db.php";
$conn = db();

$token = trim($_GET["token"] ?? "");

if ($token === "") {
  echo "<h2>Invalid verification link.</h2>";

  exit;
}

// find token
$stmt = $conn->prepare("
  SELECT user_id
  FROM mse_tokens
  WHERE token = ? AND type='email_verify' AND expire_time > NOW()
  LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
  echo "<h2>Invalid or expired token.</h2>";
  echo "<p><a href='index.php?page=login'>Go to login</a></p>";
  exit;
}

$userId = (int)$row["user_id"];

// Check if user is already verified
$upd = $conn->prepare("UPDATE mse_users SET is_verified=1 WHERE id=?");
$upd->bind_param("i", $userId);
$upd->execute();
$upd->close();

// Delete the used token
$del = $conn->prepare("DELETE FROM mse_tokens WHERE token=? AND type='email_verify'");
$del->bind_param("s", $token);
$del->execute();
$del->close();

echo "<h2>Your email has been successfully verified </h2>";
echo "<p><a href='index.php?page=login'>Click here to log in</a></p>";
