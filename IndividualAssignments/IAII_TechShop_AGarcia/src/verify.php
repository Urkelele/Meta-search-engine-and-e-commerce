<?php
$db = DB::get();

if (empty($_GET['token'])) {
    echo "<h2>Invalid verification link.</h2>";
    return;
}

$token = $_GET['token'];

// Look for token in table
$stmt = $db->prepare("SELECT user_id FROM tokens WHERE token = ? AND type = 'email_verify' AND expire_time > NOW()");
if (!$stmt) {
    echo "<h2>Server error (prepare failed).</h2>";
    return;
}

$stmt->bind_param("s", $token);
if (!$stmt->execute()) {
    $stmt->close();
    echo "<h2>Server error (execute failed).</h2>";
    return;
}

$res = $stmt->get_result();
$row = $res->fetch_assoc();

$user_id = (int)$row['user_id'];

if (empty($user_id)) {
    echo "<h2>Invalid or expired token.</h2>";
    return;
}

// Mark user as verified
$upd = $db->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
if ($upd) {
    $upd->bind_param("i", $user_id);
    $upd->execute();
    $upd->close();
}

// Delete token
$del = $db->prepare("DELETE FROM tokens WHERE token = ? AND type ='email_verify'");
if ($del) {
    $del->bind_param("s", $token);
    $del->execute();
    $del->close();
}

echo "<h2>Your email has been successfully verified!</h2>";
echo "<p><a href='index.php?page=login'>Click here to log in</a></p>";

?>