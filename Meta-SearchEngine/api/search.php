<?php
header("Content-Type: application/json");

// --------------------------------------------------
// Load IA configuration
// --------------------------------------------------
$ias = require "../includes/ia_config.php";

// Get search text
$q = $_GET['q'] ?? "";

// Final result array
$results = [];

// --------------------------------------------------
// Call each IA search endpoint
// --------------------------------------------------
foreach ($ias as $ia_name => $ia) {

    $url = $ia['base_url'] . "search.php?q=" . urlencode($q);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "X-API-KEY: " . $ia['api_key']
        ],
        CURLOPT_TIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        continue; // IA not reachable
    }

    $data = json_decode($response, true);

    if (!isset($data['items'])) {
        continue;
    }

    // --------------------------------------------------
    // Normalize results
    // --------------------------------------------------
    foreach ($data['items'] as $item) {
        $results[] = [
            "ia"       => $ia_name,
            "item_id"  => $item['id'],
            "name"     => $item['name'],
            "price"    => $item['price'],
            "stock"    => $item['stock'],
            "category" => $item['category'],
            "image"    => $item['image']
        ];
    }
}

// --------------------------------------------------
// Output unified result
// --------------------------------------------------
echo json_encode([
    "success" => true,
    "items"   => $results
]);
