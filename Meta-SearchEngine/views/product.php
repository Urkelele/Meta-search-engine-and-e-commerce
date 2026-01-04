<?php
$ias = require __DIR__ . "/../includes/ia_config.php";

$base = $GLOBALS['BASE'] ?? '';

$ia = $_GET["ia"] ?? "";
$id = (int)($_GET["id"] ?? 0);

// Checks if the item is in the database
if ($ia === "" || $id <= 0 || !isset($ias[$ia])) {
  header("Location: {$base}/public/index.php?page=404");
  exit;
}

$ia_conf = $ias[$ia];

function ia_get_json(string $url, string $apiKey, int &$httpCode = null): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      "Accept: application/json",
      "X-API-KEY: " . $apiKey
    ],
    CURLOPT_TIMEOUT => 8,
  ]);

  $raw = curl_exec($ch);
  $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $json = json_decode($raw, true);
  return is_array($json) ? $json : ["success" => false, "error" => "Invalid JSON", "_raw" => $raw];
}

function join_ia_path(string $baseUrl, string $path): string {
  if ($path === "") return "";
  if (preg_match('~^https?://~i', $path)) return $path;

  $parts  = parse_url($baseUrl);
  $scheme = $parts["scheme"] ?? "http";
  $host   = $parts["host"] ?? "localhost";
  $port   = isset($parts["port"]) ? ":" . $parts["port"] : "";

  // /Meta-search-engine-and-e-commerce/
  $basePath = $parts["path"] ?? "/";
  $projectPrefix = "";
  if (preg_match('~^(.*?/)?IndividualAssignments/.*$~', $basePath)) {
    $projectPrefix = preg_replace('~(.*?/)?IndividualAssignments/.*$~', '$1', $basePath);
    if ($projectPrefix === $basePath) $projectPrefix = "";
  }

  if ($projectPrefix !== "" && str_starts_with($path, $projectPrefix)) {
    return $scheme . "://" . $host . $port . $path;
  }

  if ($projectPrefix !== "" && str_starts_with($path, "/IndividualAssignments/")) {
    return $scheme . "://" . $host . $port . rtrim($projectPrefix, "/") . $path;
  }

  return $scheme . "://" . $host . $port . $path;
}


// fetch item details from IA
$itemUrl = rtrim($ia_conf["base_url"], "/") . "/item.php?id=" . urlencode((string)$id);
$http = 0;
$resp = ia_get_json($itemUrl, $ia_conf["api_key"], $http);

if ($http !== 200 || empty($resp["success"])) {
  $msg = htmlspecialchars($resp["error"] ?? "IA item.php failed");
  echo "<h2>Could not load product</h2>";
  echo "<p>{$msg}</p>";
  echo "<p><a href='{$base}/public/index.php?page=home'>Back to home</a></p>";
  exit;
}

$src = $resp["item"] ?? [];

// TechShop (tu item.php) devuelve estos campos dentro de item:
$name        = (string)($src["name"] ?? ("Item #" . $id));
$description = (string)($src["description"] ?? "");
$price       = (float)($src["price"] ?? 0);
$shipping    = (float)($src["shipping_price"] ?? 0);
$stock       = (int)($src["stock"] ?? 0);

$category   = (string)($src["category"] ?? "");
$properties = is_array($src["properties"] ?? null) ? $src["properties"] : [];

// Image
$imageUrl = (string)($src["image"] ?? "");
$imageUrl = $imageUrl !== "" ? join_ia_path($ia_conf["base_url"], $imageUrl) : null;

$userLogged = !empty($_SESSION["user"]["id"]);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($name) ?></title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial; margin:0; background:#fafafa; color:#111}
    .wrap{max-width:1050px; margin:0 auto; padding:22px}
    .card{background:#fff; border:1px solid #e6e6e6; border-radius:12px; padding:18px}
    .grid{display:grid; grid-template-columns: 360px 1fr; gap:22px}
    .imgbox{width:100%; aspect-ratio: 4/3; border:1px solid #e6e6e6; border-radius:10px; overflow:hidden; background:#f2f2f2; display:flex; align-items:center; justify-content:center}
    .imgbox img{width:100%; height:100%; object-fit:cover; display:block}
    .muted{color:#666}
    .price{font-size:22px; font-weight:800}
    .ok{color:#1b7f2a; font-weight:700}
    .bad{color:#b00020; font-weight:700}
    .row{display:flex; gap:10px; align-items:center; flex-wrap:wrap}
    input[type=number]{padding:10px; width:90px; border:1px solid #ddd; border-radius:8px}
    button{padding:10px 14px; border:0; border-radius:10px; cursor:pointer; font-weight:700}
    .btn-add{background:#28a745; color:#fff}
    .btn-buy{background:#007bff; color:#fff}
    .btn-disabled{background:#bbb; color:#fff; cursor:not-allowed}
    .alert{margin-top:12px; padding:10px 12px; border-radius:10px; border:1px solid #eee; background:#fff7e6}
    a{color:#0b63ce; text-decoration:none}
    ul{margin:0; padding-left:18px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="grid">
        <div>
          <div class="imgbox">
            <?php if ($imageUrl): ?>
              <img src="<?= htmlspecialchars($imageUrl) ?>"
                   alt="<?= htmlspecialchars($name) ?>"
                   onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=&quot;muted&quot;>No image</div>';"/>
            <?php else: ?>
              <div class="muted">No image</div>
            <?php endif; ?>
          </div>

          <p class="muted" style="margin:10px 0 0;">
            Store: <strong><?= htmlspecialchars($ia) ?></strong> · ID: <strong><?= (int)$id ?></strong>
          </p>
        </div>

        <div>
          <h1 style="margin:0 0 6px;"><?= htmlspecialchars($name) ?></h1>

          <?php if ($category): ?>
            <p class="muted" style="margin:4px 0;">Category: <strong><?= htmlspecialchars($category) ?></strong></p>
          <?php endif; ?>

          <?php if (!empty($properties)): ?>
            <p class="muted" style="margin:10px 0 6px;">Properties:</p>
            <ul>
              <?php foreach ($properties as $prop): ?>
                <li><?= htmlspecialchars(is_array($prop) ? json_encode($prop) : (string)$prop) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php if ($description): ?>
            <p class="muted" style="margin:10px 0;"><?= nl2br(htmlspecialchars($description)) ?></p>
          <?php endif; ?>

          <div style="margin:14px 0;">
            <div class="price">€<?= number_format($price, 2) ?></div>
            <div class="muted">Shipping: <strong>€<?= number_format($shipping, 2) ?></strong></div>
            <div style="margin-top:6px; font-size:18px; font-weight:800;">
              Total: €<?= number_format($price + $shipping, 2) ?>
            </div>
          </div>

          <p style="margin:10px 0;">
            Available stock:
            <?php if ($stock > 0): ?>
              <span class="ok"><?= (int)$stock ?></span>
            <?php else: ?>
              <span class="bad">Out of stock</span>
            <?php endif; ?>
          </p>

          <div id="msg" class="alert" style="display:none;"></div>

          <div class="row" style="margin-top:14px;">
            <label for="qty">Quantity:</label>
            <input id="qty" type="number" min="1" value="1" <?= $stock > 0 ? 'max="'.$stock.'"' : 'disabled' ?> />
            <button id="btnAdd" class="<?= $stock>0 ? 'btn-add' : 'btn-disabled' ?>" <?= $stock>0 ? '' : 'disabled' ?>>
              Add to cart
            </button>
            <button id="btnBuy" class="<?= $stock>0 ? 'btn-buy' : 'btn-disabled' ?>" <?= $stock>0 ? '' : 'disabled' ?>>
              Buy now
            </button>
          </div>

          <?php if (!$userLogged): ?>
            <div class="alert" style="margin-top:14px;">
              To add products to the cart you need to log in.
              <a href="<?= htmlspecialchars($base) ?>/public/index.php?page=login">Go to login</a>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>

<script>
const BASE = <?= json_encode($base) ?>;
const userLogged = <?= $userLogged ? "true" : "false" ?>;

function showMsg(text) {
  const el = document.getElementById("msg");
  el.style.display = "block";
  el.textContent = text;
}

async function addToCart(buyNow=false) {
  if (!userLogged) {
    showMsg("You need to log in to add items to the cart.");
    return;
  }

  const qty = Math.max(1, parseInt(document.getElementById("qty").value || "1", 10));
  const payload = {
    ia: <?= json_encode($ia) ?>,
    item_id: <?= (int)$id ?>,
    quantity: qty
  };

  const res = await fetch(BASE + "/api/cart/add.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(payload),
    credentials: "same-origin"
  });

  const json = await res.json().catch(() => ({}));

  if (!res.ok || !json.success) {
    showMsg(json.error || "Could not add item to cart.");
    return;
  }

  window.location.href = buyNow
    ? (BASE + "/public/index.php?page=checkout")
    : (BASE + "/public/index.php?page=cart");
}

document.getElementById("btnAdd")?.addEventListener("click", () => addToCart(false));
document.getElementById("btnBuy")?.addEventListener("click", () => addToCart(true));
</script>
</body>
</html>
