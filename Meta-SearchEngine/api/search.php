<?php
header("Content-Type: application/json; charset=utf-8");

$ias = require __DIR__ . "/../includes/ia_config.php";

$q           = trim($_GET['q'] ?? "");
$category    = trim($_GET['category'] ?? "");
$subcategory = trim($_GET['subcategory'] ?? "");

// general IA GET helper
function ia_get(string $url, string $apiKey, int &$http = null): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      "Accept: application/json",
      "X-API-KEY: " . $apiKey
    ],
    CURLOPT_TIMEOUT => 8
  ]);
  $raw = curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $json = json_decode($raw, true);
  return is_array($json) ? $json : ["success" => false, "error" => "Invalid JSON", "_raw" => $raw];
}

// Normalize properties to array of strings
// - TechShop: ["Mechanical","RGB"]
// - TTRPG: {"material":"Acrylic","theme":"Galaxy"}  -> ["material:Acrylic","theme:Galaxy"]
function normalize_properties($props): array {
  if (is_array($props)) {
    $isAssoc = array_keys($props) !== range(0, count($props) - 1);
    if ($isAssoc) {
      $out = [];
      foreach ($props as $k => $v) {
        if ($v === null || $v === '') continue;
        $out[] = $k . ":" . $v;
      }
      return $out;
    }
    $out = [];
    foreach ($props as $v) {
      if ($v === null || $v === '') continue;
      $out[] = (string)$v;
    }
    return $out;
  }
  return [];
}

// search on all IAs
$rawItems = [];
foreach ($ias as $ia_name => $ia) {
  $url = rtrim($ia['base_url'], "/") . "/search.php?q=" . urlencode($q);

  $http = 0;
  $data = ia_get($url, $ia['api_key'], $http);

  if ($http !== 200 || !isset($data['items']) || !is_array($data['items'])) {
    continue;
  }

  foreach ($data['items'] as $item) {
    $rawItems[] = [
      "ia"       => $ia_name,
      "item_id"  => (int)($item['id'] ?? 0),
      "name"     => (string)($item['name'] ?? ''),
      "price"    => (float)($item['price'] ?? 0),
      "stock"    => (int)($item['stock'] ?? 0),
      "category" => (string)($item['category'] ?? ''),
      "image"    => (string)($item['image'] ?? ''),
      // properties will be filled later if needed
    ];
  }
}

// build categories and subcategories info
$categoriesSet = [];
$subcatsByCat  = [];
$seenCatCount = [];

foreach ($rawItems as $it) {
  $cat = $it["category"];
  if ($cat !== "") $categoriesSet[$cat] = true;
}

foreach ($rawItems as $it) {
  $cat = $it["category"];
  if ($cat === "") continue;

  $seenCatCount[$cat] = ($seenCatCount[$cat] ?? 0) + 1;
  if ($seenCatCount[$cat] > 12) continue; // performance limit

  $ia_conf = $ias[$it["ia"]];
  $itemUrl = rtrim($ia_conf["base_url"], "/") . "/item.php?id=" . urlencode((string)$it["item_id"]);

  $http = 0;
  $full = ia_get($itemUrl, $ia_conf["api_key"], $http);
  if ($http !== 200 || empty($full["success"]) || empty($full["item"])) continue;

  $props = normalize_properties($full["item"]["properties"] ?? []);
  foreach ($props as $p) {
    if ($p === "") continue;
    $subcatsByCat[$cat][$p] = true;
  }
}

// normalize arrays
$categories = array_keys($categoriesSet);
sort($categories, SORT_NATURAL | SORT_FLAG_CASE);

$subcatsByCatOut = [];
foreach ($subcatsByCat as $cat => $set) {
  $arr = array_keys($set);
  sort($arr, SORT_NATURAL | SORT_FLAG_CASE);
  $subcatsByCatOut[$cat] = $arr;
}

// filter by category and subcategory
$filtered = [];
foreach ($rawItems as $it) {
  if ($category !== "" && strcasecmp($it["category"], $category) !== 0) {
    continue;
  }

  if ($subcategory !== "") {
    // fetch properties if not already done
    $ia_conf = $ias[$it["ia"]];
    $itemUrl = rtrim($ia_conf["base_url"], "/") . "/item.php?id=" . urlencode((string)$it["item_id"]);

    $http = 0;
    $full = ia_get($itemUrl, $ia_conf["api_key"], $http);
    if ($http !== 200 || empty($full["success"]) || empty($full["item"])) {
      continue;
    }

    $props = normalize_properties($full["item"]["properties"] ?? []);
    $it["properties"] = $props;

    $match = false;
    foreach ($props as $p) {
      if (strcasecmp($p, $subcategory) === 0) { $match = true; break; }
    }
    if (!$match) continue;
  }

  $filtered[] = $it;
}

echo json_encode([
  "success" => true,
  "items"   => $filtered,
  "meta"    => [
    "categories" => $categories,
    "subcategories_by_category" => $subcatsByCatOut
  ]
], JSON_UNESCAPED_UNICODE);
