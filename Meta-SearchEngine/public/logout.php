<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../../includes/session.php";

logout_user();
echo json_encode(["success"=>true]);