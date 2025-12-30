<?php
session_start();
$_SESSION = [];
session_destroy();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$base = preg_replace('#/api/auth$#', '', $base);

header("Location: " . $base . "/public/index.php?logout=1");
exit;
