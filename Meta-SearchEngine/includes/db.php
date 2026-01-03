<?php
function db(): mysqli {
  static $conn = null;
  if ($conn instanceof mysqli) return $conn;

  $conn = new mysqli("127.0.0.1", "root", "", "meta_search_engine");

  if ($conn->connect_error) {
    http_response_code(500);
    die("DB connection failed: " . $conn->connect_error);
  }

  $conn->set_charset("utf8mb4");
  return $conn;
}

$conn = db();
